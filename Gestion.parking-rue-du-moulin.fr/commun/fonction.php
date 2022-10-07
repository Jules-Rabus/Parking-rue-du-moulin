<?php
				
	require 'App/PHPMailer/src/Exception.php';
	require 'App/PHPMailer/src/PHPMailer.php';
	require 'App/PHPMailer/src/SMTP.php';

	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;
    
	
	$code = code();

 function heure(){
	date_default_timezone_set('Europe/Paris');
	$soleil = strtotime(date_sunset(time(), SUNFUNCS_RET_STRING, 49.375, 2.1935, 85, 2));
	$heure = strtotime(date('H:i'));
	$jour = date('l');
	$midi = strtotime('12:00:00');
	$aprem = strtotime('16:00:00');
	$soir = strtotime('19:00:00');
	
	if ($heure > $soleil || $heure > $soir ){
		$debut = 'Bonsoir';
		$fin = 'Bonne soirée';		
	}
	else{
		$debut = 'Bonjour';
		$fin = 'Bonne journée';
	}

	if( $heure < $aprem && $heure > $midi  ){
		$fin = 'Bon après midi';
	}
	if( $heure > $aprem && $heure < $soir && $heure < $soleil){
		$fin = 'Bonne fin de journée';
	}
	if ($jour == 'Friday' && ($heure > $soleil && $heure > $soir)){
		$fin = 'Bon week-end';
	}
	if ($jour == 'Sunday' && $heure < $midi){
		$fin = 'Bon dimanche';
	}
	if ($jour == 'Monday' && $heure > $midi && !($heure < $soleil || $heure < $soir)){
		$fin = 'Bonne semaine';
	}
	
	return array($debut,$fin,$soleil);
}

function formation_message($duree,$place){

	if($duree > 1){
		$duree_message = $duree . " jours";
	}
	elseif($duree == 1){
		$duree_message = $duree . " jours";
	}
	
	if($place > 1){
		$place_message = "de " . $place .  " places";
	}
	elseif($place == 1){
		$place_message = "d&#x27;une place" ;
	}	

	return array($duree_message,$place_message);
}

 function message($form,$date,$datef,$place,$contact,$code){

	$duree = duree($date,$datef);
	$tarif =  prix($duree,$place);
	 
	$heure = heure();
	$message_formation = formation_message($duree,$place);

	$date_test = $date;
	$date_sql = $date;
	$datef_sql = $datef;
	$date = date('d/m', abs(strtotime($date))+43000);
	$datef = date('d/m', abs(strtotime($datef))+43000);

	$serveur = "x";
	$login = "x";
	$password = "x";
	$bdd = "x";
	$connexion =  mysqli_connect($serveur,$login, $password, $bdd);
	if ($connexion->connect_error) {
		die("Connection échoué: " . $connexion->connect_error);
	}
	$client_recherche = $connexion->query("SELECT COUNT(*) FROM reservation WHERE contact LIKE '$contact' ");
	while( $client_resultat = mysqli_fetch_array($client_recherche)){
			$client = $client_resultat[0];
		}
	
	if ($form == 0){		
		$message = $heure[0] . ",%0A%0ADésole je ne peux pas prendre votre réservation. Le parking étant plein pour ces dates.%0A%0A$heure[1] à vous,%0ACordialement Mr Rabus";
	}
	if ($form == 1){		
		$message = $heure[0] . ",%0A%0AJe vous confirme votre réservation " . $message_formation[1] . " de parking du $date au $datef au tarif de $tarif €.%0AJe vous remercie de me recontacter 48H00 avant votre arrivée au parking afin d&#x27;obtenir le code d&#x27;accès au parking.%0AEtês-vous déjà venu au parking ?%0A%0A$heure[1] à vous,%0ACordialement Mr Rabus";
	}
	if ($form == 1 && $client > 1){		
		$message = $heure[0] . ",%0A%0AJe vous confirme votre réservation " . $message_formation[1] . " de parking du $date au $datef au tarif de $tarif €.%0AJe vous remercie de me recontacter 48H00 avant votre arrivée au parking afin d&#x27;obtenir le code d&#x27;accès au parking.%0A%0A$heure[1] à vous,%0ACordialement Mr Rabus";
	}
	if ($form == 1 && strtotime(date("Y-m-d"))+43000 + (86400 * 5) > strtotime($date_test) ){	
		$sql = "UPDATE reservation SET code  = '$code' WHERE date LIKE '$date_sql' AND datef LIKE '$datef_sql' AND contact LIKE '$contact' AND place LIKE '$place' ";
		$connexion->query($sql);
		$message = $heure[0] . ",%0A%0AJe vous confirme votre réservation " . $message_formation[1] . " de parking du $date au $datef au tarif de $tarif €.%0AVotre code d&#8217;accès au parking sera le : $code %0AEtês-vous déjà venu au parking ? %0A%0A$heure[1] à vous,%0ACordialement Mr Rabus";
	}
	if ($form == 1 && strtotime(date("Y-m-d"))+43000 + (86400 * 5) > strtotime($date_test) && $client >= 1){	
		$sql = "UPDATE reservation SET code  = '$code' WHERE date LIKE '$date_sql' AND datef LIKE '$datef_sql' AND contact LIKE '$contact' AND place LIKE '$place' ";
		$connexion->query($sql);
		$message = $heure[0] . ",%0A%0AJe vous confirme votre réservation " . $message_formation[1] . " de parking du $date au $datef au tarif de $tarif €.%0AVotre code d&#8217; accès au parking sera le : $code . %0A%0A$heure[1] à vous,%0ACordialement Mr Rabus";
	}
	if ($form == -1){		
		$message = $heure[0] . ",%0A%0AJe vous confirme l&#x27;annulation de votre réservation " . $message_formation[1] . " place de parking du $date au $datef.%0A%0A$heure[1] à vous,%0ACordialement Mr Rabus";
	}
	if ($form == 2 ){
	    // .%0ASuite a de récents problèmes de voiture mal stationnée, je vous demande de bien respecter le marquage au sol sur le parking pour que tout le monde trouve sa place.

		$message = $heure[0] . ",%0A%0AVotre code d&#x27;accès sera le : $code.%0AJe vous rappelle également que le paiement se fait par chèque ou espèce à l&#x27;arrivée sur le parking via des enveloppes pré-remplies.%0A%0A$heure[1] à vous,%0ACordialement Mr Rabus";
		$sql = "UPDATE reservation SET code  = '$code' WHERE date LIKE '$date_sql' AND datef LIKE '$datef_sql' AND contact LIKE '$contact' AND place LIKE '$place' ";
		$connexion->query($sql);
	}
	if ($form == 2 && $client >= 2 ){
		$message = $heure[0] . ",%0A%0AVotre code d&#x27;accès sera le : $code.%0A%0A$heure[1] à vous,%0ACordialement Mr Rabus";
		$sql = "UPDATE reservation SET code  = '$code' WHERE date LIKE '$date_sql' AND datef LIKE '$datef_sql' AND contact LIKE '$contact' AND place LIKE '$place' ";
		$connexion->query($sql);
	}
	if ($form == 3){		
		$message = $heure[0] . ",%0A%0AJe vous confirme l&#x27;allongement de votre réservation jusqu&#x27;au $datef au tarif de $tarif €.%0A%0A%0A$heure[1] à vous,%0ACordialement Mr Rabus";
	}
	if ($form == 4){		
		$message = $heure[0] . ",%0A%0ALe parking se situe entre le 17 et le 19 rue du moulin à Tillé (portail noir) à 650m à pied de l&#x27;aéroport.%0AL&#x27;accès au parking se fait via un portail motorisé à digicode.%0ALe paiement s&#x27;éffectue à votre arrivée au parking au moyen d&#x27;enveloppes pré remplies disponibles à l&#x27;entrée du parking du parking et à déposer dans la boite aux lettres située le long du grillage.
		 Bien sûr le paiement se fait en espèce ou par chèque bancaire et dépend de la période de stationnement et du nombre de véhicule.%0AVous restez en possession des clés de votre véhicule.%0ASi vous avez des questions n&#x27;hésitez pas.%0ASi vous voulez je vous confirme votre réservation " . $message_formation[1] . " de parking du $date au $datef au tarif de $tarif €.%0A%0A$heure[1] à vous,%0ACordialement Mr Rabus";
	}

    if($form == 6){
        $message = $heure[0] . ",%0A%0ALe parking se situe entre le 17 et le 19 rue du moulin à Tillé (portail noir) à 650m à pied de l&#x27;aéroport.%0AL&#x27;accès au parking se fait via un portail motorisé à digicode.%0ALe paiement s&#x27;éffectue à votre arrivée au parking au moyen d&#x27;enveloppes pré remplies disponibles à l&#x27;entrée du parking du parking et à déposer dans la boite aux lettres située le long du grillage.
		 Bien sûr le paiement se fait en espèce ou par chèque bancaire et dépend de la période de stationnement et du nombre de véhicule.%0AVous restez en possession des clés de votre véhicule.%0ASi vous avez des questions n&#x27;hésitez pas.%0A%0AJe vous confirme votre réservation " . $message_formation[1] . " de parking du $date au $datef au tarif de $tarif €.%0A%0A$heure[1] à vous,%0ACordialement Mr Rabus";
    }

	if ($form == 5){		
		$message = $heure[0] . ",%0A%0ASi vous voulez je vous confirme votre réservation " . $message_formation[1] . " de parking du $date au $datef au tarif de $tarif € .%0A%0A$heure[1] à vous,%0ACordialement Mr Rabus";
	}

	return $message;
 }

 function message_v2($form,$date,$datef,$place,$contact,$code){

	$duree = duree($date,$datef);
	$tarif =  prix($duree,$place);
	 
	$heure = heure();
	$message_formation = formation_message($duree,$place);

	$date_test = $date;
	$date_sql = $date;
	$datef_sql = $datef;
	$date = date('d/m', abs(strtotime($date))+43000);
	$datef = date('d/m', abs(strtotime($datef))+43000);

	private $host = "xxxxx";
    private $db_name = "xxxx";
    private $username = "xxxxx";
    private $password = "xxxxx";
	$connexion =  mysqli_connect($serveur,$login, $password, $bdd);
	if ($connexion->connect_error) {
		die("Connection échoué: " . $connexion->connect_error);
	}
	$client_recherche = $connexion->query("SELECT COUNT(*) FROM reservation WHERE contact LIKE '$contact' ");
	while( $client_resultat = mysqli_fetch_array($client_recherche)){
			$client = $client_resultat[0];
		}
	
	if ($form == 0){		
		$message = $heure[0] . ",%0A%0ADésole je ne peux pas prendre votre réservation. Le parking étant plein pour ces dates.%0A%0A$heure[1] à vous,%0ACordialement Mr Rabus";
	}
	if ($form == 1){		
		$message = $heure[0] . ",%0A%0ALe parking se situe entre le 17 et le 19 rue du moulin à Tillé (portail noir) à 650m à pied de l&#x27;aéroport.%0AL&#x27;accès au parking se fait via un portail motorisé à digicode.%0ALe paiement s&#x27;éffectue à votre arrivée au parking au moyen d&#x27;enveloppes pré remplies disponibles à l&#x27;entrée du parking du parking et à déposer dans la boite aux lettres située le long du grillage.
		 Bien sûr le paiement se fait en espèce ou par chèque bancaire et dépend de la période de stationnement et du nombre de véhicule.%0AVous restez en possession des clés de votre véhicule.%0ASi vous avez des questions n&#x27;hésitez pas.%0AJe vous confirme votre réservation " . $message_formation[1] . " de parking du $date au $datef au tarif de $tarif €.%0A%0A$heure[1] à vous,%0ACordialement Mr Rabus";
	}
	if ($form == 1 && $client > 1){		
		$message = $heure[0] . ",%0A%0AJe vous confirme votre réservation " . $message_formation[1] . " de parking du $date au $datef au tarif de $tarif €.%0AJe vous remercie de me recontacter 48H00 avant votre arrivée au parking afin d&#x27;obtenir le code d&#x27;accès au parking.%0A%0A$heure[1] à vous,%0ACordialement Mr Rabus";
	}
	if ($form == 1 && strtotime(date("Y-m-d"))+43000 + (86400 * 5) > strtotime($date_test) ){	
		$sql = "UPDATE reservation SET code  = '$code' WHERE date LIKE '$date_sql' AND datef LIKE '$datef_sql' AND contact LIKE '$contact' AND place LIKE '$place' ";
		$connexion->query($sql);
		$message = $heure[0] . ",%0A%0AJe vous confirme votre réservation " . $message_formation[1] . " de parking du $date au $datef au tarif de $tarif €.%0AVotre code d&#x27;accès au parking sera le : $code %0AEtês-vous déjà venu au parking ? %0A%0A$heure[1] à vous,%0ACordialement Mr Rabus";
	}
	if ($form == 1 && strtotime(date("Y-m-d"))+43000 + (86400 * 5) > strtotime($date_test) && $client >= 1){	
		$sql = "UPDATE reservation SET code  = '$code' WHERE date LIKE '$date_sql' AND datef LIKE '$datef_sql' AND contact LIKE '$contact' AND place LIKE '$place' ";
		$connexion->query($sql);
		$message = $heure[0] . ",%0A%0AJe vous confirme votre réservation " . $message_formation[1] . " de parking du $date au $datef au tarif de $tarif €.%0AVotre code d&#x27;accès au parking sera le : $code . %0A%0A$heure[1] à vous,%0ACordialement Mr Rabus";
	}
	if ($form == -1){		
		$message = $heure[0] . ",%0A%0AJe vous confirme l&#x27;annulation de votre réservation " . $message_formation[1] . " place de parking du $date au $datef.%0A%0A$heure[1] à vous,%0ACordialement Mr Rabus";
	}
	if ($form == 2 ){
		$message = $heure[0] . ",%0A%0AVotre code d&#x27;accès sera le : $code.%0AJe vous rappelle également que le paiement se fait par chèque ou espèce à l&#x27;arrivée sur le parking via des enveloppes pré-remplies.%0ASuite a de récents problèmes de voiture mal stationnée, je vous demande de bien respecter le marquage au sol sur le parking pour que tout le monde trouve sa place.%0A%0A$heure[1] à vous,%0ACordialement Mr Rabus";
		$sql = "UPDATE reservation SET code  = '$code' WHERE date LIKE '$date_sql' AND datef LIKE '$datef_sql' AND contact LIKE '$contact' AND place LIKE '$place' ";
		$connexion->query($sql);
	}
	if ($form == 2 && $client >= 2 ){
		$message = $heure[0] . ",%0A%0AVotre code d&#x27;accès sera le : $code.%0ASuite a de récents problèmes de voiture mal stationnée, je vous demande de bien respecter le marquage au sol sur le parking pour que tout le monde trouve sa place.%0A%0A$heure[1] à vous,%0ACordialement Mr Rabus";
		$sql = "UPDATE reservation SET code  = '$code' WHERE date LIKE '$date_sql' AND datef LIKE '$datef_sql' AND contact LIKE '$contact' AND place LIKE '$place' ";
		$connexion->query($sql);
	}
	if ($form == 3){		
		$message = $heure[0] . ",%0A%0AJe vous confirme l&#x27;allongement de votre réservation jusqu&#x27;au $datef au tarif de $tarif €.%0A%0A%0A$heure[1] à vous,%0ACordialement Mr Rabus";
	}
	if ($form == 4){		
		$message = $heure[0] . ",%0A%0ALe parking se situe entre le 17 et le 19 rue du moulin à Tillé (portail noir) à 650m à pied de l&#x27;aéroport.%0AL&#x27;accès au parking se fait via un portail motorisé à digicode.%0ALe paiement s&#x27;éffectue à votre arrivée au parking au moyen d&#x27;enveloppes pré remplies disponibles à l&#x27;entrée du parking du parking et à déposer dans la boite aux lettres située le long du grillage.
		 Bien sûr le paiement se fait en espèce ou par chèque bancaire et dépend de la période de stationnement et du nombre de véhicule.%0AVous restez en possession des clés de votre véhicule.%0ASi vous avez des questions n&#x27;hésitez pas.%0ASi vous voulez je vous confirme votre réservation " . $message_formation[1] . " de parking du $date au $datef au tarif de $tarif €.%0A%0A$heure[1] à vous,%0ACordialement Mr Rabus";
	}
	if ($form == 5){		
		$message = $heure[0] . ",%0A%0ASi vous voulez je vous confirme votre réservation " . $message_formation[1] . " de parking du $date au $datef au tarif de $tarif € .%0A%0A$heure[1] à vous,%0ACordialement Mr Rabus";
	}

	return $message;
 }
 
function message_client($form,$date,$datef,$place,$email,$code){
	 
	$heure = heure();
	$duree = duree($date,$datef);
	$tarif =  prix($duree,$place);
	$date = date('m-d', abs(strtotime($date))+43000);
	$datef = date('m-d', abs(strtotime($datef))+43000);
	private $host = "xxxxx";
    private $db_name = "xxxx";
    private $username = "xxxxx";
    private $password = "xxxxx";
	$connexion =  mysqli_connect($serveur,$login, $password, $bdd);
	if ($connexion->connect_error) {
		die("Connection échoué: " . $connexion->connect_error);
	}
	$id = $_SESSION['id'];
	
	$client_recherche = $connexion->query("SELECT COUNT(*) FROM reservation WHERE contact LIKE '$email' ");
	while( $client_resultat = mysqli_fetch_array($client_recherche)){
			$client = $client_resultat[0];
		}
	
		$sql = " SELECT * FROM utilisateur WHERE id LIKE '$id'";
	$resultat = $connexion->query($sql);
	while( $ra = mysqli_fetch_array($resultat)){
		$nom = $ra['nom'];
	}			
	
	if($duree >= 2){
		$duree_message = "$duree jours";
	}
	elseif($duree == 1){
		$duree_message = "$duree jour";
	}
	
	if($place >= 2){
		$place_message = "$place places";
	}
	elseif($place == 1){
		$place_message = "$place place";
	}	
	
	if ($form == 0){		
		$message = "<h1> Malheuresement le parking est rempli à ces dates <h1><br><h1> Le tarif aurait été de $tarif € pour $duree_message et pour $place_message </h1><br>";
	}
	if ($form == 1){		
		$message = "<h1> Le tarif pour $duree_message et pour $place_message est de $tarif € </h1><br><h1> Votre réservation du $date au $datef à bien été enregistré </h1><br>";
		$sujet = "Confirmation de votre réservation";
	$message_mail = "Bonjour, Nous avons bien reçu votre demande de réservation du $date au $datef pour $place_message au tarif de $tarif €. A très bientôt.Contact : Adresse e-mail : elonico7@hotmail.fr Numéro de téléphone : 06.71.73.18.35 . Ne pas répondre à cet e-mail automatique.";	
	$message_html = "<!DOCTYPE html>
<html lang='fr' style ='font-family: 'Calibri';font-size: 62.5%;margin: 1rem;color:black;'>
<head>
    <meta http-equiv='Content-Type' content='text/html; charset='UTF-8' />
    <title>Confirmation de votre réservation Parking-privé-rue-du-moulin</title>
</head>


<body>
    <main>
        <article style='border-style: solid;border-color: grey;padding: 3rem;text-align:center;color:black;' class='texte'>
            <h1 style='font-size: 2rem;color:black;'>Confirmation de votre réservation</h1>

            <p style='font-size: 1.4rem;margin: 1rem;text-decoration: none;color:black;' >$heure[0] Madame / Monsieur $nom , <br><br>
                	<h2 style='font-size: 1.2rem;color:black;'><ul style=' list-style-type:unset;'>
			<li style='font-size:1.6rem;margin:0.3vh;color:black;list-style-type:unset;'>Date d'arrivée : $date</li>
			<li style='font-size:1.6rem;margin:0.3vh;color:black;list-style-type:unset;'>Date de départ : $datef</li>
			<li style='font-size:1.6rem;margin:0.3vh;color:black;list-style-type:unset;'>Nombre de place : $place_message</li>
			<li style='font-size:1.6rem;margin:0.3vh;color:black;list-style-type:unset;'>Durée de stationement : $duree_message</li>
			<li style='font-size:1.6rem;margin:0.3vh;color:black;list-style-type:unset;'>Tarif : $tarif €</li>
		</ul><br>
	</h2>
			
			<div style=' background-color: darkred;font-size: 1.5rem; padding: 0.5rem;display: inline;text-align: center;'>
			<a style=' padding: 1rem;color: white;text-decoration: none; ' href='https://parking-rue-du-moulin.fr' class='bouton'>Accédez au site du parking</a>
			</div>
			
	</p>
			
        </article>
			

        <article style='  text-decoration: none;text-align:center;color:black;' class='contact'>
            <p style='    font-size: 1rem;text-decoration: none;color:black;'>
                <strong>Adresse e-mail :</strong> <a href='mailto:elonico7@hotmail.fr'>elonico7@hotmail.fr</a><br>
                <strong>Numéro de téléphone :</strong> <a href='sms:+33671731835'>06.71.73.18.35</a><br><br>
				<p   style='font-size: 1.3rem;color:black;'><strong>Parking-privé-rue-du-moulin</strong></p>
            </p>
        </article>
		
		<footer style='text-align:center;'>
                <p style='    font-size: 1rem;' class='pas_repondre'>Ne pas répondre à cet e-mail automatique</p>
          </footer>

    </main>

    </body>
</html>

";
		
		email($email,$nom,$sujet,$message_html,$message_mail);
	}
	if ($form == 1 && $client >= 1){		
		$message = "<h1> Le tarif pour $duree_message et pour $place_message est de $tarif € </h1><br><h1> Votre réservation du $date au $datef à bien été enregistré </h1><br>";
			email($email,$nom,$sujet,$message_html,$message);
	}
	if ($form == 1 && (date('Y-m-d', abs(strtotime(date("Y-m-d"))+43000) + (86400 * 5))) > $date ){	
		$sql = "UPDATE reservation SET code  = '$code' WHERE date LIKE '$date' AND datef LIKE '$datef' AND contact LIKE '$email' AND place LIKE '$place' ";
		$connexion->query($sql);
		
		$message = "<h1> Le tarif pour $duree_message et pour $place_message est de $tarif € </h1><br><h1> Votre réservation du $date au $datef à bien été enregistré </h1><br>";
			email($email,$nom,$sujet,$message_html,$message);
	}
	
	if ($form == 1 && (date('Y-m-d', abs(strtotime(date("Y-m-d"))+43000) + (86400 * 5))) > $date && $client >= 1){	
		$sql = "UPDATE reservation SET code  = '$code' WHERE date LIKE '$date' AND datef LIKE '$datef' AND contact LIKE '$email' AND place LIKE '$place' ";
		$connexion->query($sql);
		
		$message = "<h1> Le tarif pour $duree_message et pour $place_message est de $tarif € </h1><br><h1> Votre réservation du $date au $datef à bien été enregistré </h1><br>";
	}
	if ($form == -1){		
		$message = "<h1> Votre réservation du $date au $datef a bien été annulée";
	
		$sujet = "Confirmation de l'annulation de votre réservation";
	$message_mail = "Bonjour, Nous avons bien reçu votre d'annulation de réservation du $date au $datef pour $place_message. A très bientôt.Contact : Adresse e-mail : elonico7@hotmail.fr Numéro de téléphone : 06.71.73.18.35 . Ne pas répondre à cet e-mail automatique.";	
	$message_html = "
        <article style='border-style: solid;border-color: grey;padding: 3rem;text-align:center;color:black;' class='texte'>
            <h1 style='font-size: 2rem;color:black;'>Confirmation de l'annulation de votre réservation</h1>

            <p style=' font-size: 1.4rem;margin: 1rem;text-decoration: none;color:black;' >$heure[0] Madame / Monsieur $nom , <br><br>
                	<h2 style='font-size: 1.2rem;color:black;'><ul style=' list-style-type:unset;'>
			<li style='font-size:1.6rem;margin:0.3vh;color:black;list-style-type:unset;'>Date d'arrivée : $date</li>
			<li style='font-size:1.6rem;margin:0.3vh;color:black;list-style-type:unset;'>Date de départ : $datef</li>
			<li style='font-size:1.6rem;margin:0.3vh;color:black;list-style-type:unset;'>Nombre de place : $place_message</li>
			<li style='font-size:1.6rem;margin:0.3vh;color:black;list-style-type:unset;'>Durée de stationement : $duree_message</li>
		</ul><br>
	</h2>
			<div style=' background-color: darkred;font-size: 1.5rem; padding: 0.5rem;display: inline;text-align: center;'>
			<a style=' padding: 1rem;color: white;text-decoration: none; ' href='https://parking-rue-du-moulin.fr' class='bouton'>Accédez au site du parking</a>
			</div>
			
			</p>

			
        </article>
			

        <article style='  text-decoration: none;text-align:center;color:black;' class='contact'>
            <p style='    font-size: 1rem;text-decoration: none;color:black;'>
                <strong>Adresse e-mail :</strong> <a href='mailto:elonico7@hotmail.fr'>elonico7@hotmail.fr</a><br>
                <strong>Numéro de téléphone :</strong> <a href='sms:+33671731835'>06.71.73.18.35</a><br><br>
				<p   style='font-size: 1.3rem;color:black;'><strong>Parking-privé-rue-du-moulin</strong></p>
            </p>
        </article>
		
		<footer style='text-align:center;'>
                <p style='    font-size: 1rem;' class='pas_repondre'>Ne pas répondre à cet e-mail automatique</p>
          </footer>

    </main>
";
	email($email,$nom,$sujet,$message_html,$message_mail);
		
	}
	if ($form == 2 ){
		$message = "$heure[0],%0A%0AVotre code d&#x27;accès sera le : $code.%0AJe vous rappelle également que le paiement se fait par chèque ou espèce à l&#x27;arrivée sur le parking via des enveloppes pré-remplies.%0A%0A$heure[1] à vous,%0ACordialement Mr Rabus";
		$sql = "UPDATE reservation SET code  = '$code' WHERE date LIKE '$date' AND datef LIKE '$datef' AND contact LIKE '$email' AND place LIKE '$place' ";
		$connexion->query($sql);
	}
	if ($form == 2 && $client >= 2 ){
		$message = "$heure[0],%0A%0AVotre code d&#x27;accès sera le : $code.%0A%0A$heure[1] à vous,%0ACordialement Mr Rabus";
		$sql = "UPDATE reservation SET code  = '$code' WHERE date LIKE '$date' AND datef LIKE '$datef' AND contact LIKE '$email' AND place LIKE '$place' ";
		$connexion->query($sql);
	}
	if ($form == 3){		
		$message = "
	<h2><ul>
			<li>Date d'arrivée : $date</li>
			<li>Date de départ : $datef</li>
			<li>Nombre de place : $place</li>
			<li>Durée de stationement : $duree_message</li>
			<li>Tarif : $tarif €</li>
		</ul><br>
	</h2>
	";
	}
	
	return $message;
 }
 
 
 
function prix($duree,$place){

	if($duree < 5){
		$tarif = array(1 => 5 , 2 => 8, 3 => 10 , 4 => 10 );
		$prix = $tarif[$duree];
	}
	if($duree > 4 && $duree < 29){

		$prix = 10;
		$duree -= 4;
		$prix += round($duree/2,0,PHP_ROUND_HALF_UP) * 5;
	}
	if( $duree > 28){
		$prix = 70;
		$duree -= 29;
		$prix += round($duree/5,0,PHP_ROUND_HALF_UP) * 5;
	}

	return $prix * $place;
}

function duree($date,$datef){
	$date = new DateTime($date);
	$datef = new DateTime($datef);
	return ($date->diff($datef)) ->days +1;
}

function reservation($nom,$contact,$date,$datef,$place,$code,$action){

	// rajouter id_reservation
	
	private $host = "xxxxx";
    private $db_name = "xxxx";
    private $username = "xxxxx";
    private $password = "xxxxx";
	$connexion =  mysqli_connect($serveur,$login, $password, $bdd);
	if ($connexion->connect_error) {
		die("Connection échoué: " . $connexion->connect_error);
	}
	$id = $_SESSION['id'];
	
	$recherche = $connexion->query("SELECT COUNT(*) FROM reservation WHERE nom = '$nom' AND contact = '$contact' AND date = '$date' AND datef = '$datef' AND place = '$place' AND id = $id  ");
	while( $reservation_= mysqli_fetch_array($recherche)){
			$reservation_existe = $reservation_[0];
	}
	
	if($action == 1){

		if (dispo_boucle($date,$datef,$place) && $date < $datef ){
		
			$duree = duree($date,$datef);

			while($duree != 0 ){
				$duree --;
				$datee = date('Y-m-d', abs(strtotime($date)+43000) + (86400 * $duree));	
				place($datee,$place);
			}	
			$code = " INSERT INTO reservation(id,nom,contact,date,datef,place,code)
					VALUES($id,'$nom','$contact','$date','$datef','$place','$code')";	
			if ( $connexion->query($code) === TRUE){
				return 1;
			}
		}
		else{
			return 0;
		}
		
	}
	elseif($action == -1 && $reservation_existe >= 1 ){
		
		$duree = duree($date,$datef);
		$place = -$place;

		while($duree != 0){
			$duree--;
			$datee = date('Y-m-d', abs(strtotime($date)+43000) + (86400 * $duree));	
			place($datee,$place);
		}	
		$place = -$place;
		$code = "DELETE FROM reservation WHERE nom = '$nom' AND contact = '$contact' AND date = '$date' AND datef = '$datef' AND place = '$place' AND id = $id ";
		if ($connexion->query($code) === TRUE){
			return -1;
		}
	}
}

function allonger($nom,$contact,$date,$datef_initiale,$datef_nouvelle,$place,$code){

	if ( dispo_boucle($datef_initiale,$datef_nouvelle,$place) && strtotime ($datef_initiale) < strtotime ($datef_nouvelle) ){
		$retour = reservation($nom,$contact,$date,$datef_initiale,$place,$code,-1);
		if( $retour == -1){
			$retour = reservation($nom,$contact,$date,$datef_nouvelle,$place,$code,1);
			if ( $retour == 1){
				return 1;
			}
		}
	}
	else{
		return 0;
	}
}

function changement_nom($nom,$contact,$date,$datef,$place,$id_reservation){

	require "bdd.php";

	$sql = "UPDATE reservation SET nom  = '$nom' WHERE contact = '$contact' AND date = '$date' AND datef = '$datef' AND place = '$place' AND idreservation = $id_reservation";
	$nombre_ligne = $connexion->query($sql);
	return $nombre_ligne->rowCount();

}

function dispo_boucle($date,$datef,$place){

	$duree = duree($date,$datef);

    $place_min = 40;

	while($duree != 0){
		$duree --;
		$datee = date('Y-m-d', abs(strtotime($date)+43000) + (86400 * $duree));
        $place_dispo = place_disponible($datee,$place);
		if( !$place_dispo ){
			return 0;
		}
        else{
            if($place_min > $place_dispo ){
                $place_min = $place_dispo;
            }
        }
	}
	return $place_min;
}

function place($date,$place){
	
	require "bdd.php";

	$place_dispo = place_disponible($date,$place);
	$place_parking = place_parking($date);
	
	if($place_dispo){
		$place_parking = $place_parking - $place;
		$code = "UPDATE date SET place = $place_parking WHERE date = '$date'";
		$connexion->query($code);	
	}
		
}

function mouvement($date,$action){
	
	require "bdd.php";

	if ( $action == 1){ // arrivée

		$code = "SELECT place FROM reservation WHERE date LIKE '$date' ";	
		$requete = $connexion->query($code);
		$places = $requete -> fetchAll(PDO::FETCH_OBJ);
	}
	else{
		
		$code = "SELECT place FROM reservation WHERE datef LIKE '$date' ";	
		$requete = $connexion->query($code);
		$places = $requete -> fetchAll(PDO::FETCH_OBJ);
	}

	$resultat = 0;

	foreach( $places as $place ){
		$resultat += $place->place;
	}

	return $resultat;
	
}

function place_parking($date){

	require "bdd.php";
	
	$place_parking = 40;	
			
	$code = "SELECT COUNT(*) as count FROM date WHERE date LIKE '$date' ";	
	$resultat = $connexion->query($code);
	$date_existance = $resultat -> fetch(PDO::FETCH_ASSOC)['count'];	

	if ($date_existance == 0){
		$code = "INSERT INTO date(date,place) VALUES('$date',$place_parking)";
		$connexion->exec($code);		
		return $place_parking;
	}

	if ($date_existance == 1){	
		$code =   "SELECT place FROM date WHERE date LIKE '$date' ";	
		$resultat = $connexion->query($code);
		return $resultat -> fetch(PDO::FETCH_ASSOC)['place'];
	}

}

function place_disponible($date,$place){
	
	$place_parking = place_parking($date);

    $place_dispo = $place_parking - $place;
	
	if($place_dispo > 0){
		return $place_dispo;
	}
	else{
		return 0;
	}
}

function jours_francais($date){

	setlocale(LC_TIME, "fr_FR");
	return utf8_encode(ucfirst(strftime("%A", strtotime($date))));
}

function mois_francais($date){

	setlocale(LC_TIME, "fr_FR");
	return utf8_encode(ucfirst(strftime("%B", strtotime($date))));
}

function argent(){

	require "bdd.php";

	$argent = 0;

	$code =	"SELECT date,datef,place FROM reservation  ";
	$resultat = $connexion->query($code);

	while( $res = $resultat -> fetch(PDO::FETCH_ASSOC)){
		$date = $res['date'];
		$datef = $res['datef'];
		$place = $res['place'];
		$duree = duree($date,$datef);
		$argent += prix($duree,$place);
	}
	return $argent;
}

function argent_mois($date){
	
	require "bdd.php";

	$argent = 0;
	$mois_date = date('Y-m%', strtotime($date));

	$code =	"SELECT date,datef,place FROM reservation WHERE DATE LIKE '$mois_date'";
	$resultat = $connexion->query($code);

	while( $res = $resultat -> fetch(PDO::FETCH_ASSOC)){
		$date = $res['date'];
		$datef = $res['datef'];
		$place = $res['place'];
		$duree = duree($date,$datef);
		$argent += prix($duree,$place);
	}
	return $argent;
}

function code(){

	require "bdd.php";

	$code =	"SELECT code FROM code "; 
	$resultat = $connexion->query($code);
	return $resultat -> fetch(PDO::FETCH_ASSOC)['code'];

}

function nom_page_mamie(){

	$page = "Mystère";

	switch ($_SERVER['REQUEST_URI']) {
		case "/mamie.php?tom=2104":
			$page = "Planning";
			break;
		case "/recherche_mamie.php?tom=2104":
			$page = "Recherche";
			break;
		default:
		
	}

	return $page;

}

function nom_page(){

	$page = "Mystère";

	switch ($_SERVER['REQUEST_URI']) {
		case "/?tom=2104":
			$page = "Réserver";
			break;
		case "/index.php?tom=2104":
			$page = "Réserver";
			break;
		case "/planning.php?tom=2104":
			$page = "Planning";
			break;
		case "/recherche.php?tom=2104":
			$page = "Recherche";
			break;
		case "/acces.php?tom=2104":
			$page = "Code";
			break;
		case "/statistique.php?tom=2104":
			$page = "Statistique";
			break;
		default:
		if (strstr( $_SERVER['REQUEST_URI'], "/message.php?tom=2104")){
			$page = "Message";
		}
		
	}

	return $page;

}

function conversion_europe_contact($contact){

	if( !filter_var($contact,FILTER_VALIDATE_EMAIL)){

		if ($contact[0] == 0 && ($contact[1] == 6 || $contact[1] == 7)){
			$contact=str_replace(' ','',$contact);
			return substr_replace($contact,"+33",0,1);
		}
		elseif( strstr($contact,"+33")){
			$contact=str_replace(' ','',$contact);
			return $contact;
		}
		elseif ( strlen($contact) < 9 || strlen($contact) > 11){
			return $contact;
		}
		else{
			return 0;
		}
	}
	return trim($contact);
	
}

function email($email,$nom,$sujet,$message_html,$message){
	$mail = new PHPMailer(true);

	try {
		//Server settings
		$mail->isSMTP();                                            // Send using SMTP
		$mail->Host       = 'x';                    // Set the SMTP server to send through
		$mail->SMTPAuth   = true;                                   // Enable SMTP authentication
		$mail->Username   = 'x';                     // SMTP username
		$mail->Password   = 'x';                               // SMTP password
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
		$mail->Port       = 25;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
		
		//Recipients
		$mail->setFrom('reservation@parking-rue-du-moulin.fr', 'Parking-rue-du-moulin.fr');
		$mail->addAddress($email, $nom);     // Add a recipient

		// Content
		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject = $sujet;
		$mail->Body    = $message_html;
		$mail->AltBody = $message;
		$mail->CharSet = 'UTF-8';

		$mail->send();
		return array("<h1>Le mail a bien été envoyé, veuillez consulter vos mails.</h1><br>",1);
	} catch (Exception $e) {
		return array("<h1>Le mail n'a pas pu etre envoyé, si le problème persiste veuillez nous contacter.</h1><br>",0);
	}
}
?>