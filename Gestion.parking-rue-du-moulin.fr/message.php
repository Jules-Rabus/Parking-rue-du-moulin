<?php	

    require 'commun/header_cahier.php';
    session_start();

    if ( isset($_GET['contact']) ) {

        $contact = $_GET['contact'];
        $contact_europe = conversion_europe_contact($contact);

        if( filter_var($contact,FILTER_VALIDATE_EMAIL) ){
            echo "<a class='tel' href='mailto:$contact'>Mail : $contact</a> ";
        }
        else{
            $contact_europe = conversion_europe_contact($contact);
            if($contact_europe == 0 ){
                header('Location:/message.php?tom=2104');
            }
            echo "<a class='tel' href='sms:$contact'>Contact : $contact_europe</a> ";
        }

        require "commun/bdd.php";

        echo "<div class='flex'><table>";

        $code =   "SELECT nom,date,datef,code FROM reservation WHERE contact LIKE '$contact' OR contact LIKE '$contact_europe' ";
        $resultat = $connexion->query($code);
		while(  $res = $resultat -> fetch(PDO::FETCH_ASSOC)){
            $nom = $res['nom'];
            $date = $res['date'];
            $datef = $res['datef'];
            $code = $res['code'];
            if ( $code == 0){
                echo "<tr class='code_r'><td>$nom</td><td>$date</td><td>$datef</td></tr>  ";
            }
            else{
                echo "<tr class='code_v'><td>$nom</td><td>$date</td><td>$datef</td></tr>  ";
            }
        }


        echo "</table>";

        echo "         
        <form action='message.php?tom=2104&contact=$contact ' method='post'>
            <div>
                <input type='checkbox' id='bonjour' name='bonjour' checked>
                <label for='bonjour'>Bonjour</label>
            </div>
            <div>
                <input type='checkbox' id='code' name='code'>
                <label for='code'>Code</label>
            </div>
            <div>
                <input type='checkbox' id='explication' name='explication'>
                <label for='explication'>Explication</label>
            </div>
            <div>
                <input type='checkbox' id='cordialement' name='cordialement' checked>
                <label for='cordialement'>Cordialement</label>
            </div>
            <br>
            <input type='submit' value='Valider'>
        </form>	
        
        </div>
        ";

        if( (isset($_POST['bonjour']) || isset($_POST['code']) || isset($_POST['explication']) || isset($_POST['cordialement'])) ){
        
            echo "<script>
            function copier() {
              var texte = document.getElementById('texte');
              texte.select();
              texte.setSelectionRange(0, 99999);
              navigator.clipboard.writeText(texte.value);
            }
            </script>
            ";
    
            $phrase = "nop";
            if( $_POST['bonjour'] == "on"){
                $phrase = heure()[0];
            }
            echo "<input type='text' value='$phrase' id='texte'>
                <button onclick='copier()'>Copier</button>
            ";
        }

    }
    else{
    echo"
        <form action='message.php' method='get'>
            <label for='contact'>Contact :</label>
            <input name='tom' type='hidden' value='2104'>
            <input name='contact' type='text'>
            <br>
            <input type='submit' value='Valider'>
        </form>	";
    }

?>