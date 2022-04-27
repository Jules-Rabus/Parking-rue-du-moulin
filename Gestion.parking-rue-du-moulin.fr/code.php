<?php

 if (! $_GET['tom'] == 2104 ){
	header('Location:http://google.com/');
	exit();

}
	 
	require "commun/fonction.php";

	$contact = htmlspecialchars($_POST['contact']);
	$id_reservation = htmlspecialchars($_POST['id_reservation']);
	$datef = htmlspecialchars($_POST['datef']);
	$date = htmlspecialchars($_POST['date']);
	$place = htmlspecialchars($_POST['place']);
	$phrase = heure();

	require "commun/bdd.php";

	$sql = "SELECT code FROM reservation WHERE date LIKE '$date' AND datef LIKE '$datef' AND idreservation LIKE '$id_reservation' AND place LIKE '$place'  ";	
	$resultat = $connexion->query($sql);
	$res = $resultat -> fetch(PDO::FETCH_ASSOC);
	$code_r = $res['code'];
		
	if( $code_r == 0 ){
			$texte = message(2,$date,$datef,$place,$contact,$code);
			if (filter_var($contact,FILTER_VALIDATE_EMAIL)){
				$mail = "mailto:$contact?subject=Code parking&body=$texte";
				echo "<meta http-equiv='refresh' content='0; url=$mail'>";
			}
			else{
				$sms = "sms:$contact?body=$texte";
				echo "<meta http-equiv='refresh' content='0; url=$sms'>";
			}
	}
	else{
		header("Location: sms:$contact");
	}		
?>