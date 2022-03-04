<?php

    function chargerClasse($classe)
    {
        $classe=str_replace('\\','/',$classe);
        require $classe . '.php';
    }

    spl_autoload_register('chargerClasse'); //Autoload

    use App\Model\ReservationModel;

    require('commun/fonction.php');


    if(!empty($_POST['contact'])){

        $contact = conversion_europe_contact(htmlspecialchars($_POST['contact']));
        $reservation = (new ReservationModel())->findOne("COUNT(*) as count",array('contact'=>$contact))['count'];

        if($reservation){
            $nom_contact = (new ReservationModel())->findOne("nom",array('contact'=>$contact))['nom'];
        }

    }

    if(!empty($_POST['nom'])){
        $nom_contact = $_POST['nom'];
    }

    if(!empty($_POST['date']) && !empty($_POST['datef']) && !empty($_POST['place']) && strtotime ($_POST['datef']) >= strtotime($_POST['date']) ){

        $date = htmlspecialchars($_POST['date']);
        $datef = htmlspecialchars($_POST['datef']);
        $place = htmlspecialchars($_POST['place']);
        $tarif = prix(duree($date,$datef),$place);
        $tarif = $tarif . " €";

        $disponible = dispo_boucle($date,$datef,$place);

        if(!empty($_POST['contact'])){

            if($reservation){
                $message = message(5,$date,$datef,$place,$contact,$code);
            }
            else{
                $message = message(4,$date,$datef,$place,$contact,$code);
            }
        }

    }

?>
<div>
    <table>
        <thead>
        <tr>
            <th>Prix</th>
            <th>Disponible</th>
            <?php if(isset($contact)) : ?>
                <th>Nombre réservation</th>
                <th>Nom</th>
            <?php endif; ?>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><?php if(isset($tarif)) : ?><?=$tarif?><?php endif; ?></td>
            <td><?php if(isset($disponible)) : ?><?=$disponible?><?php endif; ?></td>
            <?php if(isset($contact)) : ?>
                <td><?php if(isset($reservation)) : ?><?=$reservation?><?php endif; ?></td>
                <td><?php if(isset($nom_contact)) : ?><?=$nom_contact?><?php endif; ?></td>
            <?php endif; ?>
        </tr>
        </tbody>

        <?php if( isset($tarif,$disponible,$contact) && ( isset($nom) || isset($nom_contact) )) : ?>
            <tfoot>
            <td colspan="4">
                <button type="submit" form="formulaire_reservation" >Réserver</button>
            </td>
            </tfoot>
        <?php endif; ?>

    </table>

    <?php if(!empty($contact)) : ?>
        <div>
            <?php if (filter_var($contact,FILTER_VALIDATE_EMAIL)) : ?>
                <a href='mailto:<?=$contact?>?subject=Confirmation%20de%20votre%20réservation&body=<?=$message?>'>Si vous Mail</a>
            <?php else : ?>
                <a href='sms:<?=$contact?>?body=<?=$message?>'>Si vous Tel</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>
