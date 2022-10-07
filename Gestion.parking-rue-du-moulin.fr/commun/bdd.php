 <?php   
	if(!defined("SERVEUR")) {
	define("SERVEUR","xxxxx");
	define("USER","xxxxx");
	define("MDP","xxxxx");
	define("BDD","xxxxx");
	}

    try {
        $connexion= new PDO('mysql:host='.SERVEUR.';dbname='.BDD, USER, MDP);
		$connexion->exec("SET CHARACTER SET utf8");
    }
    catch(Exception $e)
	{
		echo 'Erreur : '.$e->getMessage().'<br />';
		echo 'N° : '.$e->getCode();
	}

?>