<?php	

	require "commun/header_cahier_mamie.php";
			
	require "commun/bdd.php";

	// numform 1: affichage, numform 2 : changer, numform 3 : allonger, numform 4 : code numform -1 :annuler
	

	// réduire * select

	if ( !empty($_POST['date']) && !empty($_POST['numform']) && $_POST['numform'] == 1  ) {

		$date = htmlspecialchars($_POST['date']);

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

?>

	<?php if( empty($_POST['numform']) ) :?>
		
	<h2>Formulaire de recherche:</h2>

		<form action='recherche_mamie.php?tom=2104' method='post'>
			<p>
				<label for='date'>Date de recherche :</label>
				<input type='date' id='date' name='date' value='<?= date("Y-m-d")?>' required>
				<input type='number' name='numform' value=1 hidden>
			</p>
			<p class="submit">
				<input type='submit' value='Valider''> 
			</p>
		</form>	

	<?php endif; ?>

	<?php if( !empty($_POST['date']) && !empty($_POST['numform']) && $_POST['numform'] == 1) :?>
		<table class='table'>
			<tr>
				<td>
					<form action='recherche_mamie.php?tom=2104' method='post'>
						<input name='date' value='<?=$date_moins ?>' hidden required>
						<input type='number' name='numform' value=1 hidden>
						<p class="submit">
							<input type='submit' value='<?=$date_moins ?>'>	
						</p>	
					</form>	
				</td>
				<td>
					<form action='recherche_mamie.php?tom=2104' method='post'>
						<p>
							<input type='date' name='date' value='<?=$date ?>' required>
							<input type='number' name='numform' value=1 hidden>
						</p>
						<p class="submit">
							<input type='submit' value='Valider'> 
						</p>
					</form>	
				</td>
				<td>
					<form action='recherche_mamie.php?tom=2104' method='post'>
							<input name='date' value='<?=$date_plus ?>' hidden required>
							<input type='number' name='numform' value=1 hidden>
						<p class="submit">
							<input type='submit' value='<?=$date_plus ?>' />
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
			</tr>

			<?php foreach( $arrivee as $reservation) : ?>
				<tr>
					<td><p><?=$reservation['nom'] ?></p></td>
					<td><p><?=$reservation['datef'] ?></p></td>
					<td> 		
                        <?php if(! $reservation['code']) : ?>
                            <p class='code_r'><?=$reservation['contact'] ?></p>
                        <?php else : ?>
                            <p class='code_v'><?=$reservation['contact'] ?></p>
                        <?php endif ; ?>
					</td>

					<td><p><?=$reservation['place'] ?></p></td>

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
			</tr>

			<?php foreach( $depart as $reservation) : ?>
				<tr>
					<td><p><?=$reservation['nom'] ?></p></td>
					<td><p><?=$reservation['date'] ?></p></td>

					<td> 
						<?php if (filter_var($reservation['contact'],FILTER_VALIDATE_EMAIL)) :?>
							<a class ='tel' > <?=$reservation['contact'] ?></a>
						<?php else : ?>
							<a class ='tel' > <?=$reservation['contact'] ?></a>
						<?php endif ; ?>

					</td>

					<td><p><?=$reservation['place'] ?></p></td>

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

	<!-- Rappel form -->

	<?php if (!empty($_POST['numform']) && $_POST['numform'] != 1 && $_POST['numform'] != 4 ) :?>

		<form action='recherche_mamie.php?tom=2104' method='post'>
			<p>
				<label for='date'>Date de recherche :</label>
				<input type='date' id='date' name='date' value='<?= date("Y-m-d")?>' required>
				<input type='number' name='numform' value=1 hidden>
			</p>
			<p class="submit">
				<input type='submit' value='Valider''> 
			</p>
		</form>	

	<?php endif; ?>