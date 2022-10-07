<?php
    require ('App/Config/DataBase.php');
    require ('commun/fonction.php');

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    use PHPMailer\PHPMailer\SMTP;
    use App\Config\DataBase;

    $connexion = (new DataBase())->getConnection();

    $date = date('Y-m-d', strtotime(date('Y-m-d')) + (86400 * 3));

    $requete = $connexion->prepare("SELECT * FROM reservation WHERE date = :date and code = 0 ");
    $requete->bindParam(":date",$date);
    $requete->execute();
    $reservations = $requete->fetchAll(PDO::FETCH_ASSOC);

    $requete = $connexion->prepare("SELECT * from code");
    $requete->execute();
    $code = $requete->fetch(PDO::FETCH_ASSOC)['code'];

    $count = count($reservations);
    $count_mail = 0;

    foreach ($reservations as $reservation){

        if (filter_var(trim($reservation['contact']),FILTER_VALIDATE_EMAIL)){

            $count_mail++;
            $tarif = $tarif = prix(duree($reservation['date'],$reservation['datef']),$reservation['place']);

            $requete = $connexion->prepare("UPDATE reservation SET code = :code WHERE idreservation = :idreservation");
            $requete->bindParam(":code",$code);
            $requete->bindParam(":idreservation",$reservation['idreservation']);
            $requete->execute();

            require ('App/Mail/MailCodeClient.php');

            $mail = new PHPMailer(true);

            try {
                $mail->setFrom('reservation@parking-rue-du-moulin.fr', 'Parking-rue-du-moulin');
                $mail->addAddress($reservation['contact']);     //Add a recipient
                $mail->addReplyTo('reservation@parking-rue-du-moulin.fr', 'Parking-rue-du-moulin');
                $mail->isHTML(true);                                  //Set email format to HTML
                $mail->Subject = $sujet;
                $mail->Body    = $message_html;
                $mail->CharSet = 'UTF-8';
                $mail->send();
            } catch (Exception $e) {
                mail("xxxxx", "Problème envoie mail", $reservation['contact']); // mail effacé
            }

        }

    }

    if($count){
        require('App/Mail/MailCode.php');
        mail("xxxx", $sujet, $message_html, $entete); // mail effacé
        echo $message_html;
    }