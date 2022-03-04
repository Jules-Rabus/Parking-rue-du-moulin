<?php	

	require "commun/header_cahier.php";
			
	require "commun/bdd.php";

	// numform 1: affichage, numform 2 : changer, numform 3 : allonger, numform 4 : code numform -1 :annuler
	

	// réduire * select

	if ( !empty($_GET['date']) && !empty($_GET['numform']) && $_GET['numform'] == 1  ) {

		$date = htmlspecialchars($_GET['date']);

		$date_moins = ((new DateTime($date))->sub(new DateInterval('P1D')))->format('Y-m-d') ;
		$date_plus = ((new DateTime($date))->add(new DateInterval('P1D')))->format('Y-m-d') ;

		$sql=$connexion->prepare("SELECT place FROM date WHERE date = :date"); 
        $sql->bindParam(':date',$date);
        $sql->execute();
        $nbre_place=$sql->fetch(PDO::FETCH_ASSOC)['place'];

		$sql=$connexion->prepare("SELECT * FROM reservation WHERE date = :date");
		$sql->bindParam(':date',$date);
        $sql->execute();
		$arrivee=$sql->fetchAll(PDO::FETCH_ASSOC);

		$sql=$connexion->prepare("SELECT * FROM reservation WHERE datef = :date");
		$sql->bindParam(':date',$date);
        $sql->execute();
		$depart=$sql->fetchAll(PDO::FETCH_ASSOC);

	}

	if ( !empty($_POST['numform']) && $_POST['numform'] == -1  ) {

		$id_reservation = $_POST['id_reservation'];
		$nom = $_POST['nom'];
		$contact = $_POST['contact'];
		$datef = $_POST['datef'];
		$date = $_POST['date'];
		$place = $_POST['place'];
		$retour = reservation($nom,$contact,$date,$datef,$place,$code,-1);
		$texte = message($retour,$date,$datef,$place,$contact,$code);

	}

	if ( !empty($_POST['numform']) && $_POST['numform'] == 2  ) {

		$id_reservation = $_POST['id_reservation'];
		$nom = $_POST['nom'];
		$contact = $_POST['contact'];
		$datef = $_POST['datef'];
		$date = $_POST['date'];
		$place = $_POST['place'];

		$nombre_ligne = changement_nom($nom,$contact,$date,$datef,$place,$id_reservation);

	}

	if ( !empty($_POST['numform']) && $_POST['numform'] == 3  ) {

		$id_reservation = $_POST['id_reservation'];
		$nom = $_POST['nom'];
		$contact = $_POST['contact'];
		$datef = $_POST['datef'];
		$date = $_POST['date'];
		$datef_nouvelle = $_POST['datef_nouvelle'];
		$place = $_POST['place'];

		$nombre_ligne =  allonger($nom,$contact,$date,$datef,$datef_nouvelle,$place,code());

	}

	if ( !empty($_POST['numform']) && $_POST['numform'] == 4  ) {
	   
		   $contact = htmlspecialchars($_POST['contact']);
		   $id_reservation = htmlspecialchars($_POST['id_reservation']);
		   $datef = htmlspecialchars($_POST['datef']);
		   $date = htmlspecialchars($_POST['date']);
		   $place = htmlspecialchars($_POST['place']);
		   $phrase = heure();
	   
		   $sql = "SELECT code FROM reservation WHERE date LIKE '$date' AND datef LIKE '$datef' AND idreservation LIKE '$id_reservation' AND place LIKE '$place'  ";	
		   $resultat = $connexion->query($sql);
		   $res = $resultat -> fetch(PDO::FETCH_ASSOC);
		   $code_r = $res['code'];

			$code= 0;
			   
		   if( $code_r == 0 ){
				   $texte = message(2,$date,$datef,$place,$contact,$code);
				   if (filter_var($contact,FILTER_VALIDATE_EMAIL)){
					   $mail = "mailto:$contact?subject=Code parking&body=$texte";
					   //echo "<meta http-equiv='refresh' content='0; url=$mail'>";
					   //header("Location : $mail");
					   echo $mail;
				   }
				   else{
					   /*echo $texte;
					   $texte_bis = "Votre code dacces sera le 8989";
					   $sms = htmlspecialchars("Location: sms:$contact?body= ". $texte);
					   header('Content-Type: text/html; charset=utf-8');
					   header($sms);
					   */
					  //$texte = "VOtre code d'acces sera | 2162";
					   //$texte = utf8_encode($texte);
					   //header("Location:".$sms."");
					   $sms = "sms:$contact?body=" . message(2,$date,$datef,$place,$contact,$code);
					   //echo "<meta http-equiv='refresh' content='0; url=$sms'>";
					   //header("Location : $sms");
					   echo $sms;
	   
				   }
		   }
		   else{
			   header("Location: sms:$contact");
		   }		
	
	}
?>

	<?php if( empty($_GET['numform']) ) :?>
		
	<h2>Formulaire de recherche:</h2>

		<form action='recherche.php?tom=2104' method='get'>
			<p>
				<label for='date'>Date de recherche :</label>
				<input type='date' id='date' name='date' value='<?= date("Y-m-d")?>' required>
				<input type='number' name='numform' value=1 hidden>
				<input type='number' name='tom' value='2104' hidden required>
			</p>
			<p class="submit">
				<input type='submit' value='Valider''> 
			</p>
		</form>	

	<?php endif; ?>

	<?php if( !empty($_GET['date']) && !empty($_GET['numform']) && $_GET['numform'] == 1) :?>
		<table class='table'>
			<tr>
				<td>
					<form action='recherche.php' method='get'>
						<input name='date' value='<?=$date_moins?>' hidden required>
						<input type='number' name='numform' value='1' hidden>
						<input type='number' name='tom' value='2104' hidden required>
						<p class="submit">
							<input type='submit' value='<?=$date_moins?>'>
						</p>	
					</form>	
				</td>
				<td>
					<form action='recherche.php' method='get'>
						<p>
							<input type='date' name='date' value='<?=$date?>' required>
							<input type='number' name='numform' value='1' hidden>
							<input type='number' name='tom' value='2104' hidden required>
						</p>
						<p class="submit">
							<input type='submit' value='Valider'> 
						</p>
					</form>	
				</td>
				<td>
					<form action='recherche.php' method='get'>
							<input name='date' value='<?=$date_plus?>' hidden required>
							<input type='number' name='numform' value='1' hidden>
							<input type='number' name='tom' value='2104' hidden required>
						<p class="submit">
							<input type='submit' value='<?=$date_plus?>' />
						</p>
					</form>
				</td>
			</tr>
		</table>

		<h1><?=jours_francais($date) ?> <?=$date ?> : <?=$nbre_place ?></h1>

		<br>

		<!-- début arrivee -->

		<h2>Arrivée :</h2> <br>

		<table>

			<tr>
				<th><p>Nom</p></th>
				<th><p>Départ</p></th>
				<th><p>Contact</p></th>
				<th><p>Place</p></th>
				<th><p>Action</p></th>
			</tr>

			<?php foreach( $arrivee as $reservation) : ?>
				<tr>
					<td><p><?=$reservation['nom'] ?></p></td>
					<td><p><?=$reservation['datef'] ?></p></td>

					<td> 		
						<form action='code.php?tom=2104' method='post'>
							<input type='number' name='numform' value=4 hidden>
							<input name='contact' value='<?=$reservation['contact'] ?>' hidden>
							<input name='nom' value='<?=$reservation['nom'] ?>' hidden>
							<input name='datef' value='<?=$reservation['datef'] ?>' hidden>
							<input name='date' value='<?=$reservation['date'] ?>' hidden>
							<input name='place' value='<?=$reservation['place'] ?>' hidden>
							<input name='id_reservation' value='<?=$reservation['idreservation'] ?>'  hidden>
							<p class="submit">
								<?php if(! $reservation['code']) : ?>
									<input class='code_r' type='submit' value='<?=$reservation['contact'] ?>' />
								<?php else : ?>
									<input class='code_v' type='submit' value='<?=$reservation['contact'] ?>' />
								<?php endif ; ?>
							</p>
						</form>
					</td>

					<td><p><?=$reservation['place'] ?></p></td>

					<td>

						<?php if ($reservation['nom'] == '?') : ?>

							<form action='recherche.php?tom=2104' method='post'>
								<p>
									<input name='nom' placeholder='Nom'>
								</p>
									<input type='number' name='numform' value=2 hidden>
									<input name='contact' value='<?=$reservation['contact'] ?>' hidden>
									<input name='datef' value='<?=$reservation['datef'] ?>' hidden>
									<input name='date' value='<?=$reservation['date'] ?>' hidden>
									<input name='place' value='<?=$reservation['place'] ?>' hidden>
									<input name='id_reservation' value='<?=$reservation['idreservation'] ?>'  hidden>
								<p class="submit">
									<input type='submit' value='Changer' />
								</p>
							</form>

						<?php else : ?>

							<form action='recherche.php?tom=2104' method='post'>
								<input type='number' name='numform' value=-1 hidden>
								<input name='contact' value='<?=$reservation['contact'] ?>' hidden>
								<input name='nom' value='<?=$reservation['nom'] ?>' hidden>
								<input name='datef' value='<?=$reservation['datef'] ?>' hidden>
								<input name='date' value='<?=$reservation['date'] ?>' hidden>
								<input name='place' value='<?=$reservation['place'] ?>' hidden>
								<input name='id_reservation' value='<?=$reservation['idreservation'] ?>'  hidden>
								<p class="submit">
									<input type='submit' value='Annuler' />
								</p>
							</form>

						<?php endif ; ?>

					</td>

				</tr>
			<?php endforeach; ?>

		</table>

		<!-- fin arrivée -->

		<!-- debut départ -->

		<h2>Départ :</h2> <br>

		<table>

			<tr>
				<th><p>Nom</p></th>
				<th><p>Arrivée</p></th>
				<th><p>Contact</p></th>
				<th><p>Place</p></th>
				<th><p>Action</p></th>
			</tr>

			<?php foreach( $depart as $reservation) : ?>
				<tr>
					<td><p><?=$reservation['nom'] ?></p></td>
					<td><p><?=$reservation['date'] ?></p></td>

					<td> 
						<?php if (filter_var($reservation['contact'],FILTER_VALIDATE_EMAIL)) :?>
							<a class ='tel' href='mailto:<?=$reservation['contact'] ?>' > <?=$reservation['contact'] ?></a>
						<?php else : ?>
							<a class ='tel' href='sms:<?=$reservation['contact'] ?>' > <?=$reservation['contact'] ?></a>
						<?php endif ; ?>

					</td>

					<td><p><?=$reservation['place'] ?></p></td>

					<td>

						<form action='recherche.php?tom=2104' method='post'>
							<input type= 'number' name='numform' value=3 hidden>
							<input name='contact' value='<?=$reservation['contact'] ?>' hidden>
							<input name='nom' value='<?=$reservation['nom'] ?>' hidden>
							<input name='datef' value='<?=$reservation['datef'] ?>' hidden>
							<input name='date' value='<?=$reservation['date'] ?>' hidden>
							<input name='place' value='<?=$reservation['place'] ?>' hidden>
							<input name='id_reservation' value='<?=$reservation['idreservation'] ?>'  hidden>
							<p>
								<input type='date' name='datef_nouvelle' min='<?=$reservation['datef'] ?>' value='<?=$reservation['datef'] ?>'>
							</p>
							<p class="submit">
								<input type='submit' value='Allonger' />
							</p>
						</form>

					</td>

				</tr>
			<?php endforeach; ?>

		</table>


	<?php endif; ?>

	<!-- Annulation -->

	<?php if( !empty($_POST['numform']) && $_POST['numform'] === -1) :?>

		<h1>Annulation</h1>

		<?php if( $retour === -1) :?>

			<h2>Annulation enregistré<h2>

			<?php if (filter_var($contact,FILTER_VALIDATE_EMAIL)) :?>
				<a class ='tel' href='mailto:<?=$contact ?>?subject=Confirmation%20de%20votre%20réservation&body=$texte'>Mail</a>
			<?php else : ?>
				<a class ='tel' href='sms:<?=$contact ?>?body=$texte'>Message</a>
			<?php endif ; ?>

		<?php else : ?>

			<h2>Aucune annulation effectué<h2>
			
		<?php endif; ?>

	<?php endif; ?>

	<!-- Changement -->

	<?php if( !empty($_POST['numform']) && $_POST['numform'] === 2) :?>

		<h1>Changement de nom</h1>

		<?php if( $nombre_ligne > 0 ) :?>

			<h2>Changement de nom enregistré<h2>

		<?php else : ?>

			<h2>Aucune changement effectué<h2>
			
		<?php endif; ?>

	<?php endif; ?>

	<!-- Allongement -->

	<?php if( !empty($_POST['numform']) && $_POST['numform'] === 3) :?> 

		<h1>Allongement</h1>

		<?php if( $nombre_ligne === 1 ) :?>

			<h2>Allongement enregistré<h2>

		<?php else : ?>

			<h2>Aucune changement effectué<h2>
			
		<?php endif; ?>

	<?php endif; ?>
                    