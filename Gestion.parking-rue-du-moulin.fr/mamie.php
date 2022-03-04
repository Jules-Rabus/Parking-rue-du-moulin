<?php			
		
	require 'commun/header_cahier_mamie.php';
	session_start();	

	$date = date("Y-m-d");
	$mois_date = mois_francais($date);
	$argent = argent_mois($date);

	echo "<table><tr class='inherit'><td><h2>Aujourd'hui : ",$date,"</h2></td><td class ='inherit'><a style='font-size:2rem' href='statistique.php?tom=2104'></a></td></tr>";

	if( date('d', strtotime($date)) > 03 ){
		$d = -2;
	}
	else{
		$d = 0;
	}
	echo "<tr class='inherit'>
			<td><h2>$mois_date</h2></td>
			<td class='inherit'></td>
		</tr>";

		while ( $d != 365){
			$datee = date('Y-m-d', strtotime($date) + (86400 * $d));
			$jours_date = jours_francais($datee);
			$d++;
			$place= place_parking($datee) +5;
			$arrive = mouvement($datee,1);
			$depart = mouvement($datee,-1);
			if ( date('d', strtotime($datee)) == 01){
				$mois_date = mois_francais($datee);
				echo "<tr class='inherit'>
						<td><h2>$mois_date</h2></td>
						<td class='inherit'></td>
					</tr>";
			}
			if ( $place < 6){
				echo "<tr class ='table_r' >";
			}
			elseif( $place < 21){
				echo "<tr class ='table_o' >";
			}
			else{
				echo "<tr class ='table_v' >";
			}

			echo "<td><h2>$jours_date, $datee, $place places disponibles</h2></td>
				  <td><h2>Arrivée : $arrive Départ : $depart</h2></td>
				  </tr>";
		}
	echo "</table>";
?>	