<?php			
	
	require "commun/header_cahier.php";

    require "commun/bdd.php";

	session_start();

    $compteur_code = 0;
    $trouve = 0;

    $code =	"SELECT * FROM reservation WHERE CODE != '0' ORDER BY date ";
    $resultat = $connexion->query($code);
    while( $res = $resultat->fetch() ){

        for( $i = 0 ; $i < $compteur_code; $i++ ){

            if ($tableau[$i][0] == $res['code']){

                if( $tableau[$i][1] > $res['date']){
                    $tableau[$i][1] = $res['date'];
                }
                if( $tableau[$i][2] < $res['datef']){
                    $tableau[$i][2] = $res['datef'];
                }
                $tableau[$i][3] ++;
                $trouve = 1;
            }

        }
        if( $trouve == 0){
            $tableau[$compteur_code][0] = $res['code'];
            $tableau[$compteur_code][1] = $res['date'];
            $tableau[$compteur_code][2] = $res['datef'];
            $tableau[$compteur_code][3] = 1;
            $compteur_code++;
        }
        $trouve = 0;
    }

    if( $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send']) ){

        $trouve = 0;
        do{
            $nouveau_code = rand ( 1000 , 9999 );

            foreach($tableau as $valeur){

                if ( $code == $valeur[0]){
                    $trouve = 1;
                }
            }
        }while($trouve);

        $sql = "UPDATE code SET code  = '$nouveau_code' ";
        $connexion->exec($sql);
    }

?>

    <h2> Code : <?=code()?></h2><br>
    <table>

    <?php foreach($tableau as $valeur) : ?>      
        <tr class='inherit'>
            <td>Code : <?=$valeur[0]?></td>
            <td>Date : <?=$valeur[1]?></td>
            <td>Datef : <?=$valeur[2]?></td>
            <td>Durée : <?=duree($valeur[1],$valeur[2])?> jours</td>
            <td>Nombre : <?=$valeur[3]?></td>
        </tr>
    <?php endforeach ;?>

    </table>

    <form action='acces.php?tom=2104' method='post'>
        <input type='submit' value='Nouveau code' name='envoyer' />
    </form>