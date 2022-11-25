<?php	

	require "commun/header_cahier.php";
			
	$serveur = "xxxxxx";
	$login = "xxxxx";
	$password = "xxxxx";
	$bdd = "xxxxx";
		
	$R = 'Entrez une réservation';
	
	$connexion =  mysqli_connect($serveur,$login, $password, $bdd);
	if ($connexion->connect_error) {
    die("Connection échoué: " . $connexion->connect_error);
	}
	$datem = date("Y-m-d");
	if (isset($_POST['numform']) == 0 ){
	echo "

 <h2>Formulaire de recherche:</h2>
<div>
		<form action='rechercheb.php?tom=2104' method='post'>
				<label for='date'>Date d'arrivée au parking :</label>
				<input type='date'  name='date' value='$datem' required>
				<input name='numform' type='hidden' value='1'>
				<br>
				<input type='submit' value='Valider'>
		</form>	
</div>	


";
	}
	
	
elseif(isset($_POST['numform']) && $_POST['numform'] &&  isset($_POST['date'])){

	$date = htmlspecialchars($_POST['date']);
	$jours_date = jours_francais($date);
	$datel = strtotime($date);
	$dateep = date('Y-m-d', abs($datel+43000) - 86400);
	$datees = date('Y-m-d', abs($datel+43000) + 86400);	
	
	$code =   "SELECT * FROM date WHERE date LIKE '$date' ";	
			$resultath = $connexion->query($code);
			while( $rf = mysqli_fetch_array($resultath)){
			$place = $rf['place'];
			}
	
		echo " <table class='table'><tr><td>
		<div>
		 	 <form action='rechercheb.php?tom=2104' method='post'>
				<input type='hidden'  name='date' value='$dateep' required>
				<input name='numform' type='hidden' value='1'>
				<input type='submit' value='$dateep' />			
			</form>	
		</div>
</td>
<td>
	<div>
		<form action='rechercheb.php?tom=2104' method='post'>
				<input type='date'  name='date' value='$datem' required>
				<input name='numform' type='hidden' value='1'>
				<input type='submit' value='Rechercher' />
        </form>
	</div>
</td>
<td>
	<div>
		<form action='rechercheb.php?tom=2104' method='post'>
				<input type='hidden'  name='date' value='$datees' required>
				<input name='numform' type='hidden' value='1'>
				<input type='submit' value='$datees' />

        </form>
	</div>
</td>
</table>
";
	
	// Première partie : arrivée
	
	echo "<h2> Réservation $jours_date $date : $place</h2>";
	echo "<table><h1>Arrivée:</h1>";
	$code =   "SELECT * FROM reservation WHERE date LIKE '$date' ";	
		$resultate = $connexion->query($code);
		while( $rc = mysqli_fetch_array($resultate)){
		$nom = $rc['nom'];
		$id_reservation = $rc['idreservation'];
		$datef = $rc['datef'];
		$datea = $rc['date'];
		$place = $rc['place'];
		$contact = $rc['contact'];
		$code_r = $rc['code'];
		$phrase = heure();
		
		if ( $code_r == 0 ){
			$couleur = 'code_r';
		}
		else{
			$couleur = 'code_v';
			
		}
		
		
		echo "<tr><td>";

		if( $nom == "?"){

			echo "<form action='rechercheb.php?tom=2104' method='post'>
			<p>
				<input name='numform' type='hidden' value='4'>
				<input name='contact' type='hidden' value='$contact'>
				<input name='nom' placeholder='Nom'>
				<input name='datef' type='hidden' value='$datef'>
				<input name='date' type='hidden' value='$datea'>
				<input name='place' type='hidden' value='$place'>
				<input name='id_reservation' type='hidden' value='$id_reservation'>
				<input type='submit' value='Changer' />
			</p>
		</form>";

		}
		else{
			echo "<p>$nom</p>";
		}
		
		echo "</td><td><p>$datef</p></td><td>
		
		
		<form action='code.php?tom=2104' method='post'>
			<p>
				<input name='contact' type='hidden' value='$contact'>
				<input name='nom' type='hidden' value='$nom'>
				<input name='datef' type='hidden' value='$datef'>
				<input name='datea' type='hidden' value='$datea'>
				<input name='place' type='hidden' value='$place'>
				<input name='id_reservation' type='hidden' value='$id_reservation'>
				<input class='$couleur' type='submit' value='$contact' />
			</p>
		</form>";
		
		echo "</td><td><p>$place</p></td><td>
		
		<form action='rechercheb.php?tom=2104' method='post'>
			<p>
				<input name='numform' type='hidden' value='2'>
				<input name='id_reservation' type='hidden' value='$id_reservation'>
				<input name='nom' type='hidden' value='$nom'>
				<input name='datef' type='hidden' value='$datef'>
				<input name='datea' type='hidden' value='$date'>
				<input name='placeb' type='hidden' value='$place'>
				<input name='contact' type='hidden' value='$contact'>
				<input type='submit' value='Annuler' />
			</p>
		</form>
		
		
		</td></tr>";
		

		}
		echo "</table>";
		
		// Deuxième partie : départ
			
	echo "<table><h1>Départ:</h1>";
	$code =   "SELECT * FROM reservation WHERE datef LIKE '$date' ";	
		$resultatv= $connexion->query($code);
		while( $rv = mysqli_fetch_array($resultatv)){
		$id_reservation = $rv['idreservation'];
		$nom = $rv['nom'];
		$datea = $rv['date'];
		$datef = $rv['datef'];
		$place = $rv['place'];
		$contact = $rv['contact'];
		
		echo "<tr><td><p>$nom</p></td><td><p>$datea</p></td><td>";
		
		if (filter_var($contact,FILTER_VALIDATE_EMAIL)){
		echo "<a class ='tel' href='mailto:$contact'>$contact</a>";
		}
		else{
		echo "<a class ='tel' href='sms:$contact'>$contact</a>";
		}
		
		echo "</td><td><p>$place</p></td><td>

			<form action='rechercheb.php?tom=2104' method='post'>
				<p>
					<input name='numform' type='hidden' value='3'>
					<input name='id_reservation' type='hidden' value='$id_reservation'>
					<input name='nom' type='hidden' value='$nom'>
					<input name='datef' type='hidden' value='$datef'>
					<input type='date' name='datef_nouvelle' type='hidden' min='$datef' value='$datef'>
					<input name='datea' type='hidden' value='$datea'>
					<input name='placeb' type='hidden' value='$place'>
					<input name='contact' type='hidden' value='$contact'>
					<input type='submit' value='Allonger' />
				</p>
			</form>
		
		
		</td></tr>";
		

		}
		echo "</table>";
		
		}	
		
		// Annulation
if ( isset($_POST['numform']) && $_POST['numform'] == 2) {
		
		$contact = $_POST['contact'];
		$id_reservation = $_POST['id_reservation'];
		$nom = $_POST['nom'];
		$datef = $_POST['datef'];
		$datea = $_POST['datea'];
		$placeb = $_POST['placeb'];
		$_SESSION['id_reservation'] = $id_reservation;
		$retour = reservation($nom,$contact,$datea,$datef,$placeb,$code,-1);
		$texte = message($retour,$datea,$datef,$placeb,$contact,$code);
		echo "<h2>Annulation<h2>";
		if($retour == -1){
			echo "<h2>Annulation enregistré<h2>";
			
			if (filter_var($contact,FILTER_VALIDATE_EMAIL)){
				echo "<a class ='tel' href='mailto:$contact?subject=Confirmation%20de%20votre%20réservation&body=$texte'>Mail</a>";
			}
			else{
				echo "<a class ='tel' href='sms:$contact?body=$texte'>Message</a>";
			}
		}
}

	// allongement

if ( isset($_POST['numform']) && $_POST['numform'] == 3) {		
	$contact = $_POST['contact'];
	$nom = $_POST['nom'];
	$datef_initiale = $_POST['datef'];
	$datef_nouvelle = $_POST['datef_nouvelle'];
	$date = $_POST['datea'];
	$place = $_POST['placeb'];
	$_SESSION['id'] = 1;
	$_SESSION['id_reservation'] = $_POST['id_reservation'];

	$retour = allonger($nom,$contact,$date,$datef_initiale,$datef_nouvelle,$place,$code);

		echo "<h2>Allongement<h2>";
		if($retour == 3){
			echo "<h2> Allongement enregistré <h2>";
			
			if (filter_var($contact,FILTER_VALIDATE_EMAIL)){
				echo "<a class ='tel' href='mailto:$contact?subject=Confirmation%20de%20votre%20réservation&body=$texte'>Mail</a>";
			}
			else{
				echo "<a class ='tel' href='sms:$contact?body=$texte'>Message</a>";
			}
		}
}

if ( isset($_POST['numform']) && $_POST['numform'] == 4) {		
	$contact = $_POST['contact'];
	$nom = $_POST['nom'];
	$date = $_POST['date'];
	$datef = $_POST['datef'];
	$date = $_POST['datea'];
	$place = $_POST['place'];
	$id_reservation = $_POST['id_reservation'];

	changement_nom($nom,$contact,$date,$datef,$place,$id_reservation);

	echo "<h2>Changement de nom<h2>";
	echo "<h2>Changement de nom enregistré <h2>";
		
}

?>