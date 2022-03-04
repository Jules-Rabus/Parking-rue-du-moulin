<?php			
		
	require 'commun/header_cahier.php';

	$message = "Entrez une réservation";
	$date = date("Y-m-d");
	$datef = $date;
	$place = 1;


if(isset($_POST['date'],$_POST['datef'],$_POST['place']) && strtotime ($_POST['datef']) >= strtotime($_POST['date']) ){
	
	$date = htmlspecialchars($_POST['date']);
	$datef = htmlspecialchars($_POST['datef']);
	$place = htmlspecialchars($_POST['place']);
	$tarif = prix(duree($date,$datef),$place);
	
	$duree = duree($date,$datef);

	if( dispo_boucle($date,$datef,$place)){
	$message = "<p>Tarif : $tarif €<br> Disponible : Oui</p>";
	}
	else{
	$message = "<p>Tarif : $tarif € <br> Disponible : Non</p>";
	}

	if( isset($_POST['contact']) && !empty($_POST['contact'])){

		$contact = conversion_europe_contact( htmlspecialchars($_POST['contact']));
		$texte = message(1,$date,$datef,$place,$contact,$code);
		$texte_explication = message(4,$date,$datef,$place,$contact,$code);

		if (filter_var($contact,FILTER_VALIDATE_EMAIL)){
			$message_explication = "<a class ='tel' href='mailto:$contact?subject=Confirmation%20de%20votre%20réservation&body=$texte_explication'>Explication</a>";
			$message = "<a class ='tel' href='mailto:$contact?subject=Confirmation%20de%20votre%20réservation&body=$texte'>Si vous</a>";
		}
		else{
			$message_explication = "<a class ='tel' href='sms:$contact?body=$texte_explication'>Explication</a>";
			$message = "<a class ='tel' href='sms:$contact?body=$texte'>Si vous</a>";
		}

	}
		
	if ( isset($_POST['contact'],$_POST['nom']) && !empty($_POST['contact']) && !empty($_POST['nom']) ){
		
		$nom = htmlspecialchars($_POST['nom']);
		$contact = conversion_europe_contact(htmlspecialchars($_POST['contact']));
		$retour = reservation($nom,$contact,$date,$datef,$place,0,1);
		$confirmation = "<p>Du : $date au : $datef<br>Nom : $nom , Contact : $contact<br>Tarif : $tarif</p>";
		$texte = message($retour,$date,$datef,$place,$contact,$code);

		if (filter_var($contact,FILTER_VALIDATE_EMAIL)){
			$message = "<a class ='tel' href='mailto:$contact?subject=Confirmation%20de%20votre%20réservation&body=$texte'>Mail</a>";
		}
		else{
			$message = "<a class ='tel' href='sms:$contact?body=$texte'>Message</a>";
		}
		
	}
}

?>

<h1>Formulaire de réservation:</h1>
<div>
	  <form action='index.php?tom=2104' id='formulaire_reservation' method='post'>
				<p>
					<label for='date'>Date d'arrivée au parking :</label>
					<input type='date' id='date' value='<?=$date?>' name='date' required>
				</p>
				<p>
					<label for='datef'>Date de départ du parking :</label>
					<input type='date' id='datef' value='<?=$datef?>' name='datef' required>
				</p>
				<p>
					<label for='place'>Nombre de place :</label>
					<input type='number' id='place' min='1' value='<?=$place?>' name='place' required >
				</p>
				<p>
					<label for='nom'>Nom :</label>
					<input type='text' id='nom' name='nom' >
				</p>
				<p>
					<label for='contact'> Mail/ Tel</label>
					<input type='text' id='contact' name='contact' >
				</p>
				<p class="submit">
					<input type='submit' value='Valider' />
				</p>
			
        </form>
</div>	

<?php if (isset($confirmation)) : ?>
	<p><?=$confirmation?></p>
<?php endif; ?>


<p><?=$message ?></p>

<?php if (isset($texte)) : ?>
    <script>
		function copier() {
			var texte = document.getElementById('texte');
			texte.select();
			texte.setSelectionRange(0, 99999);
			navigator.clipboard.writeText(texte.value);
		}
	</script>
	<input type='text' value='<?=htmlspecialchars_decode($texte)?>' id='texte'>
	<button onclick='copier()'>Copier message</button>
<?php endif; ?>

<?php if (isset($message_explication)) : ?>
	<p><?=$message_explication?></p>
	<input type='text' value='<?=htmlspecialchars_decode($texte_explication)?>' id='texte'>
	<button onclick='copier()'>Copier message</button>
<?php endif; ?>

