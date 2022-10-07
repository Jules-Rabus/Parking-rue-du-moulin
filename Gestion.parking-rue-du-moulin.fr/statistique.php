<?php			
	
	require 'commun/header_cahier.php';

	session_start();

	private $host = "xxxxx";
    private $db_name = "xxxx";
    private $username = "xxxxx";
    private $password = "xxxxx";

	$connexion =  mysqli_connect($serveur,$login, $password, $bdd);
	if ($connexion->connect_error) {
		die("Connection échoué: " . $connexion->connect_error);
	}
    
	echo "<br><table><tbody>";

	$date = date('Y-m',strtotime('first day of January'));
	

	for( $i = 0; $i<12 ; $i++){
		$mois = mois_francais($date);
		$annee_date = date('Y-m-d');
		$date_moins_un = date('Y-m',strtotime('-1 year',strtotime($date)));
		$annee_moins_un = date('Y',strtotime('-1 year',strtotime($date)));

		// revenu mois un
		$argent_moins_un = argent_mois($date_moins_un);
		// fin revenu mois un

		// revenu mois
		$argent_mois  = argent_mois($date);
		// fin revenu mois

		// revenu moyen
		$argent_moyen = 0;
		$date_compteur = 0;
		$date_boucle = $date;

		while( date('Y-m',strtotime($date_boucle)) > '2020-08-31' ){

			$argent_moyen += argent_mois($date_boucle); 
			$date_compteur ++;
			$date_boucle = date('Y-m',strtotime('-1 year',strtotime($date_boucle)));
	
			}
		$argent_moyen = round($argent_moyen /$date_compteur,1); 

		// fin revenu moyen

		// vehicule

		$mois_date = date('Y-m', strtotime($date));
		$code =	"SELECT AVG(place) as avg FROM (SELECT (40-place) as place FROM date WHERE date LIKE '" . $mois_date . "-__' AND date > '2020-08-31') as place";
		$resultat = $connexion->query($code);
		$vehicule = round(mysqli_fetch_array($resultat)['avg'],1);

		// fin vehicule

		// vehicule moyenne

		$mois_date = date('m', strtotime($date));
		$code =	"SELECT AVG(place) as avg FROM (SELECT (40-place) as place FROM date WHERE date like '____-" . $mois_date . "-__' AND date > '2020-08-31' and date < '$annee_date' ) as duree";
		$resultat = $connexion->query($code);
		$vehicule_moyenne = round(mysqli_fetch_array($resultat)['avg'],1);

		// fin vehicule moyenne

		// vehicule annee precedente

		$mois_date = date('Y-m%', strtotime($date_moins_un));
		$code =	"SELECT AVG(place) as avg FROM (SELECT (40-place) as place FROM date WHERE date LIKE '$mois_date' AND date > '2020-08-31') as place ";
		$resultat = $connexion->query($code);
		$vehicule_moins_un = round( mysqli_fetch_array($resultat)['avg'],1);
		
		// fin vehicule annee precedente 

		//duree moyen

		$mois_date = date('m', strtotime($date));
		$code =	"SELECT AVG(duree) as avg FROM (SELECT DATEDIFF(datef+1,date) as duree FROM reservation WHERE DATE LIKE '____-" . $mois_date . "-__' AND date > '2020-08-31') as duree";
		$resultat = $connexion->query($code);
		$duree_moyenne = round(mysqli_fetch_array($resultat)['avg'],1);

		// fin duree moyen

		// duree moyen annee precedente
		$mois_date = date('Y-m%', strtotime($date_moins_un));
		$code =	"SELECT AVG(duree) as avg FROM (SELECT DATEDIFF(datef+1,date) as duree FROM reservation WHERE DATE LIKE '$mois_date' AND date > '2020-08-31') as duree";
		$resultat = $connexion->query($code);
		$duree_moyenne_moins_un = round(mysqli_fetch_array($resultat)['avg'],1);

		// fin duree moyen annee precedente

		// duree moyenne année en cours

		$mois_date = date('Y-m%', strtotime($date));
		$code =	"SELECT AVG(duree) as avg FROM (SELECT DATEDIFF(datef+1,date) as duree FROM reservation WHERE DATE LIKE '$mois_date' AND date > '2020-08-31') as duree";
		$resultat = $connexion->query($code);
		$duree = round(mysqli_fetch_array($resultat)['avg'],1);		

		// fin duree moyenne année en cours
?>
		<tr class='inherit'>
			<td><?=$mois?></td>

			<td class ='table_o'>Véhicule :<br><?=$vehicule?></td>
			<td class ='table_o'>Revenu :<br><?=$argent_mois?></td>
			<td class ='table_o'>Durée moyenne :<br><?=$duree?></td>

			<td class ='table_v'>Véhicule <?=$annee_moins_un?> :<br><?=$vehicule_moins_un?></td>
			<td class ='table_v'>Revenu <?=$annee_moins_un?> :<br><?=$argent_moins_un?></td>
			<td class ='table_v'>Durée moyenne <?=$annee_moins_un?> :<br><?=$duree_moyenne_moins_un?></td>

			<td class ='table_b'>Revenu moyen :<br><?=$argent_moyen?></td>
			<td class ='table_b'> Véhicule moyenne :<br><?=$vehicule_moyenne?></td>
			<td class ='table_b'>Durée moyenne :<br><?=$duree_moyenne?></td>
		</tr>

<?php		
		$date = date('Y-m',strtotime($date .'+1 month'));
	}

		// client le plus fidele

		$code =	"SELECT MAX (count) as count from (SELECT COUNT(contact) as count FROM reservation GROUP BY contact) as max";
		$resultat = $connexion->query($code);
		$plus_fidele = mysqli_fetch_array($resultat)['count'];
	
		// fin client le plus fidele
	
		// nombre de client
	
		$code =	"SELECT COUNT(DISTINCT contact) as count FROM reservation";
		$resultat = $connexion->query($code);
		$client_nombre = mysqli_fetch_array($resultat)['count'];
	
		// fin nombre de client
	
		//nombre de réservation
	
		$code =	"SELECT COUNT(*) as count FROM reservation";
		$resultat = $connexion->query($code);
		$reservation_nombre = mysqli_fetch_array($resultat)['count'];
	
		// fin nombre de réservation
	
		// Avance moyen
	
		$compteur = 0;
		$duree_avance = 0;
	
		
		$code =	"SELECT AVG(duree) as avg FROM (SELECT DATEDIFF(date+1,date_reservation) as duree FROM reservation WHERE date_reservation NOT LIKE 'NULL' ) AS moyenne";
		$resultat = $connexion->query($code);
		$duree_avance = round(mysqli_fetch_array($resultat)['avg'],1);
	
		// fin avance moyen
	
		// client moyen
	
		$reservation = 0;
		$duree_moyenne = 0;
		$compteur = 0;
		$client_moyen = 0;
	
		$code =	"SELECT DISTINCT contact FROM reservation WHERE contact like '+33%'";
		$resultat = $connexion->query($code);
		while( $rc = mysqli_fetch_array($resultat)){
			
			$contact = $rc['contact'];
			$codea =	"SELECT date,datef FROM reservation WHERE contact like $contact ";
			$resultata = $connexion->query($codea);	
			while( $ra = mysqli_fetch_array($resultata)){
				$duree_moyenne += duree($ra['date'],$ra['datef']);
				$reservation ++;
				echo $reservation;
			}
	
		//$client_moyen += $duree_moyenne / $reservation;
		$compteur ++;
		$duree_moyenne = 0;
		}
	
		$client_moyen = round($client_moyen /$compteur,1);
	
		// fin client moyen
	
		// duree moyenne
	
		$code =	"SELECT AVG(duree) as avg FROM (SELECT DATEDIFF(R.datef+1,R.date) as duree FROM reservation R) AS moyenne";
		$resultat = $connexion->query($code);
		$duree_moyenne = round(mysqli_fetch_array($resultat)['avg'],1);
	
		// fin duree moyenne

?>
	
	</tbody></table>

	<table>
		<tbody>
			<tr>
				<td>Client le + fidèle : <?=$plus_fidele?></td>
				<td>Client moyen : <?=$client_moyen?></td>
				<td>Durée moyen : <?=$duree_moyenne?></td>
				<td>Nombre de client : <?=$client_nombre?></td>
				<td>Nombre de réservation : <?=$reservation_nombre?></td>
				<td>Avance moyen : <?=$duree_avance?></td>
			</tr>
		</tbody>
	</table>
