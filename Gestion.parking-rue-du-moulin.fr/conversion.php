<?php			
	require 'commun/header_cahier.php';

    private $host = "xxxxx";
    private $db_name = "xxxx";
    private $username = "xxxxx";
    private $password = "xxxxx";

    try {
        $connexion = new PDO("mysql:host=$serveur;dbname=$bdd", $login, $password);
    }
    catch(PDOException $e) {
        die("Connexion impossible à la bdd : $e ");
    }


    /*


    $connexion =  mysqli_connect($serveur,$login, $password, $bdd);

    $code =   "SELECT * FROM reservation  ";	
    $resultat = $connexion->query($code);
    while( $rc = mysqli_fetch_array($resultat)){

    $id_reservation = $rc['idreservation'];
    $contact = conversion_europe_contact($rc['contact']);
    $sql = "UPDATE reservation SET contact  = '$contact' WHERE idreservation LIKE '$id_reservation' ";
    $connexion->query($sql);
    }
    echo " fait"

    */

?>