<?php

	session_start();

	private $host = "xxxxx";
    private $db_name = "xxxx";
    private $username = "xxxxx";
    private $password = "xxxxx";

	$id = 1 ;
	$R = 'Entrez une réservation';

	$connexion =  mysqli_connect($serveur,$login, $password, $bdd);
	if ($connexion->connect_error) {
    die("Connection échoué: " . $connexion->connect_error);
	}

	$code =   "DELETE FROM date ";
	$resultatb = $connexion->query($code);
	$code = "CREATE TABLE date ( date date NOT NULL, place int DEFAULT 40 NOT NULL) ";
	$resultatb = $connexion->query($code);

	$code =   "SELECT * FROM reservation  ";
		$resultate = $connexion->query($code);
		while( $rc = mysqli_fetch_array($resultate)){
		$datef = $rc['datef'];
		$date = $rc['date'];
		$place = $rc['place'];
		$d = 0;

		$duree = intval(abs(strtotime($date)-strtotime($datef))/86400);
		$datel = strtotime($date);

		while ( $duree != -1){


			$datee = date('Y-m-d', abs($datel+43000) + (86400 * $d));
			$duree = $duree -1;
			$d = $d+1;

			$code =   "SELECT COUNT(*) FROM date WHERE date LIKE '$datee' ";
				$resultata = $connexion->query($code);
			while( $ra = mysqli_fetch_array($resultata)){
				$rb = $ra[0];
			}


	if ($rb == 0){

		$place_disponible = 40 - $place;

		if ($place_disponible >= 0){

			$code = "INSERT INTO date(date,place)
					VALUES('$datee',$place_disponible)";


		if ($connexion->query($code) === TRUE){
			// echo "Fait ";
			}
		}
	}

	if ($rb == 1){

		$code =   "SELECT * FROM date WHERE date LIKE '$datee' ";
		$resultatb = $connexion->query($code);
		while( $rc = mysqli_fetch_array($resultatb)){
		$rd = $rc['place'];
		}

		$place_disponible = $rd - $place;


		$code = "UPDATE date SET place  = $place_disponible WHERE date = '$datee' ";
		if ($connexion->query($code) === TRUE) {


		}
	}
	echo $duree,"<br>";
		}
		}

?>