<?php

    session_start();

    function chargerClasse($classe)
    {
        $classe=str_replace('\\','/',$classe);
        require $classe . '.php';
    }

    spl_autoload_register('chargerClasse'); //Autoload

    use App\Model\ReservationModel;
    
    require 'commun/header_cahier.php';
    $_SESSION['id'] = 1;


if(!empty($_POST['date']) && !empty($_POST['datef']) && !empty($_POST['place'])  && !empty($_POST['contact']) && isset($_POST['nom']) && strtotime ($_POST['datef']) >= strtotime($_POST['date']) ){

    $contact = conversion_europe_contact(htmlspecialchars($_POST['contact']));
    $reservation = (new ReservationModel())->findOne("COUNT(*) as count",array('contact'=>$contact))['count'];

    if($reservation){
        $nom_contact = (new ReservationModel())->findOne("nom",array('contact'=>$contact))['nom'];
    }

    if(!empty($_POST['nom'])){
        $nom_contact = $_POST['nom'];
    }

    $date = htmlspecialchars($_POST['date']);
    $datef = htmlspecialchars($_POST['datef']);
    $place = htmlspecialchars($_POST['place']);
    $contact = conversion_europe_contact( htmlspecialchars($_POST['contact']));
    $retour = reservation($nom_contact,$contact,$date,$datef,$place,0,1);

        $texte = message(1,$date,$datef,$place,$contact,$code);
        $texte_explication = message(6,$date,$datef,$place,$contact,$code);

    if (filter_var($contact,FILTER_VALIDATE_EMAIL)){
        $message = "<a class ='tel' href='mailto:$contact?subject=Confirmation%20de%20votre%20réservation&body=$texte'>Confirmation</a>";
        $message_explication = "<a class ='tel' href='mailto:$contact?subject=Confirmation%20de%20votre%20réservation&body=$texte_explication'>Explication</a>";
    }
    else{
        $message = "<a class ='tel' href='sms:$contact?body=$texte'>Confirmation</a>";
        $message_explication = "<a class ='tel' href='sms:$contact?body=$texte_explication'>Explication</a>";
    }

}

?>
<div class='flex'>
    <div>
        <form id='formulaire_reservation' method='POST'>
            <fieldset>
                <legend>Formulaire de réservation</legend>
                <p>
                    <label for='date'>Date d'arrivée au parking :</label>
                    <input type='date' id='date' value='<?=date("Y-m-d")?>' name='date' required>
                </p>
                <p>
                    <label for='datef'>Date de départ du parking :</label>
                    <input type='date' id='datef' value='<?=date("Y-m-d")?>' name='datef' required>
                </p>
                <p>
                    <label for='place'>Nombre de place :</label>
                    <input type='number' id='place' value='1' min='1' name='place' required >
                </p>
                <p>
                    <label for='nom'>Nom :</label>
                    <input type='text' id='nom' name='nom' >
                </p>
                <p>
                    <label for='contact'>Mail / Tel :</label>
                    <input type='text' id='contact' name='contact' >
                </p>

            </fieldset>
        </form>
    </div>
    <div id='resultat_formulaire'></div>
    <?php if(isset($message)) : ?>
        <div>
            <?=$message?>
            <?=$message_explication?>
            <a href="recherche.php?date=<?=$date?>&numform=1&tom=2104"><?=$date?></a>
        </div>
    <?php endif?>

</div>

<script src="jquery.js" type="text/javascript"></script>
<script type="text/javascript" src="ajax_formulaire.js"></script>