<?php

if ( $_GET['tom'] != 2104 ){

	//header('Location:http://google.com/');
		exit();
}
	if ( session_status() < 2){
		session_start();
		$_SESSION['id'] = 1;
	}

	require "fonction.php";


?>

<!DOCTYPE html>
<html lang="fr">

<head>
<title>Gestion-Parking-rue-du-moulin</title>
<meta charset="UTF-8">
<link rel="icon" href="commun/header/favicon.ico"/>
<link href="../css_cahier.css" rel="stylesheet" type="text/css">
<script src="commun/header/menu.js"></script>
</head>
<body>
	<header>
		<div id="ouverture_menu"> <img class="image_menu" src="commun/header/menu.svg" alt="ouverture_menu"><h1><?php echo nom_page(); ?></h1></div>
		<nav id="ferme" class="menu">
			<div id="fermeture_menu"><img class="image_menu" src="commun/header/close.svg" alt="fermeture_menu"></div>
			<a href='index.php?tom=2104'>Réserver</a>
			<a href='planning.php?tom=2104'>Planning</a>
			<a href='recherche.php?tom=2104'>Recherche</a>
			<a href='message.php?tom=2104'>Message</a> 
			<a href='acces.php?tom=2104'>Code</a>
			<a href='statistique.php?tom=2104'>Statistique</a>
		</nav>
	</header>
    <main>

