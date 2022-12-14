<?php

namespace App\Entity;

use App\Entity\Reservation;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class Message
{

    // Cette entite n'est pas present dans la BDD

    private string $Message;

    private string $Debut;

    private string $Fin;

    private Reservation $Reservation;

    private string $Sujet;

    private string $SujetCourt;

    private int $NombreReservation;

    private MailerInterface $Mailer;

    /**
     * @param \App\Entity\Reservation $Reservation
     * @param int $NombreReservation
     */
    public function __construct(\App\Entity\Reservation $Reservation, int $NombreReservation, MailerInterface $Mailer, bool $heure = false)
    {
        $this->Debut = '';
        $this->Fin = '';
        $this->Message = '';
        $this->Reservation = $Reservation;
        $this->NombreReservation = $NombreReservation;
        $this->Mailer = $Mailer;
        if($heure) $this->heure();
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->Message;
    }

    /**
     * @param string $Message
     */
    public function setMessage(string $Message): void
    {
        $this->Message = $Message;
    }

    /**
     * @return string
     */
    public function getDebut(): string
    {
        return $this->Debut;
    }

    /**
     * @param string $Debut
     */
    public function setDebut(string $Debut): void
    {
        $this->Debut = $Debut;
    }

    /**
     * @return string
     */
    public function getFin(): string
    {
        return $this->Fin;
    }

    /**
     * @param string $Fin
     */
    public function setFin(string $Fin): void
    {
        $this->Fin = $Fin;
    }

    /**
     * @return \App\Entity\Reservation
     */
    public function getReservation(): \App\Entity\Reservation
    {
        return $this->Reservation;
    }

    /**
     * @param \App\Entity\Reservation $Reservation
     */
    public function setReservation(\App\Entity\Reservation $Reservation): void
    {
        $this->Reservation = $Reservation;
    }

    /**
     * @return int
     */
    public function getNombreReservation(): int
    {
        return $this->NombreReservation;
    }

    /**
     * @param int $NombreReservation
     */
    public function setNombreReservation(int $NombreReservation): void
    {
        $this->NombreReservation = $NombreReservation;
    }

    /**
     * @return string
     */
    public function getSujet(): string
    {
        return $this->Sujet;
    }

    /**
     * @param string $Sujet
     */
    public function setSujet(string $Sujet): void
    {
        $this->Sujet = $Sujet;
    }

    /**
     * @return string
     */
    public function getSujetCourt(): string
    {
        return $this->SujetCourt;
    }

    /**
     * @param string $Sujet
     */
    public function setSujetCourt(string $Sujet): void
    {
        $this->SujetCourt = $Sujet;
    }

    // Cette fonction va permettre de generer automatiquement les formules de politesse en fonction de l'heure de la journee
    public function heure($moment = 1){

        // On recup??re les informations lies ?? l'heure de la journ??e
        $soleil = (new \DateTime())->setTimestamp(date_sun_info((new \DateTime())->getTimestamp(), 49.375, 2.1935)['sunset']);
        $heure = new \DateTime();
        $midi = (new \DateTime())->setTime(12,0,0);
        $apresMidi = (new \DateTime())->setTime(16,0,0);
        $soir = (new \DateTime())->setTime(19,0,0);
        $jour = (new \DateTime())->format('l');
        $aujourdhui = new \DateTime();

        // En fonction des informations on etablit la bonne formule de politesse

        if ($heure > $soleil || $heure > $soir ){
            $debut = "Bonsoir";
            $fin = "Bonne soir??e";
        }
        else{
            $debut = "Bonjour";
            $fin = "Bonne journ??e";
        }

        if( $heure < $apresMidi && $heure > $midi  ){
            $fin = "Bonne apr??s midi";
        }
        if( $heure > $apresMidi && $heure < $soir && $heure < $soleil){
            $fin = "Bonne fin de journ??e";
        }
        if ($jour == "Friday" && ($heure > $soleil && $heure > $soir)){
            $fin = "Bon week-end";
        }
        if ($jour == "Sunday" && $heure < $midi){
            $fin = "Bon dimanche";
        }
        if ($jour == "Monday" && $heure > $midi && !($heure < $soleil || $heure < $soir)){
            $fin = "Bonne semaine";
        }
        if($aujourdhui->format("m") == 12 && $aujourdhui->format("d") > 20){
            $fin = "Bonnes f??tes";
        }
        if($aujourdhui->format("m") == 01 && $aujourdhui->format("d") < 10){
            $fin = "Bonne ann??e";
        }

        // Moment: 1 = tout, 2 : d??but , 3 : fin
        // Des fois il peut etre utilise de mettre que la formule de politesse a la fin, si on a deja eu la personne dans la journee afin de ne pas se repeter

        switch ($moment) {
            case 1:
                $this->Debut = $debut . ",%0a%0a";
                $this->Fin = "%0a%0a" . $fin . " ?? vous,%0aCordialement M. Rabus";
                break;
            case 2:
                $this->Debut = $debut . ",%0a%0a";
                break;
            case 3:
                $this->Fin = "%0a%0a" . $fin . " ?? vous,%0aCordialement M. Rabus";
                break;
        }

    }

    // On cree le lien pour un mail
    public function getMessageMail() : string{
        $message = str_replace(' ','%20',($this->Debut . $this->Message . $this->Fin));
        $sujet = str_replace(' ','%20', $this->Sujet);
        return 'href=mailto:' . $this->contact() . '?subject=' . $sujet . '&body=' . $message ;
    }

    // On creer le lien pour un message
    public function getMessageTelephone(bool $href = true) : string{
        $message = str_replace(' ','%20',($this->Debut . $this->Message . $this->Fin));
        if($href) return 'href=sms:' . $this->contact() . ';?&body=' . $message ;

        return 'sms:' . $this->contact() . ';?&body=' . $message ;
    }

    // On recupere le bon contact : mail/telephone
    public function contact() : string{

        if($client = $this->Reservation->getClient()){
            if( $email = $client->getEmail()){
                return $email;
            }
            return $client->getTelephone();
        }

        return $this->getReservation()->getTelephone();

    }

    // template pour envoyer un code
    public function messageCode(){

        $this->Sujet = "V??tre code d'acc??s au parking";
        $this->SujetCourt = "Code";
        $this->Message = "V??tre code d'acc??s sera le : " . $this->Reservation->getCodeAcces()->getCode();

        if($this->NombreReservation < 2 ){
            $this->Message = $this->Message . " , il sera ??galement valable pour v??tre retour.%0A%0AJe vous rappelle ??galement que le paiement se fait soit par ch??que ?? l'ordre de M.Rabus/Mme Rabus ou en esp??ces ?? l'arriv??e sur le parking via des enveloppes pr??-remplies.";
        }

    }

    // fonction pour mettre un 's' en fonction du nombre de vehicule
    public function place() : string{
        $nombrePlace = $this->Reservation->getNombrePlace();
        if( $nombrePlace > 1){
            return "de " . $nombrePlace .  " places";
        }
        return "d'une place" ;

    }

    // template pour l'explication du fonctionnement du parking
    public function messageExplication(){

        $this->Sujet = "Explication du fonctionnement";
        $this->SujetCourt = "Explication";
        $this->Message = "Le parking se situe entre le 17 et le 19 rue du moulin ?? Till?? (portail noir) ?? 650 m??tres ?? pied de l'a??roport.%0AL'acc??s au parking se fait via un portail motoris?? ?? digicode. " .
            "Je vous remercie de me recontacter par sms/mail/telephone 48h00 avant v??tre arriv??e au parking afin d'obtenir v??tre code d'acc??s, il sera ??galement valable pour v??tre retour.%0A" .
            "Le paiement s'??ffectue ?? v??tre arriv??e au moyen d'enveloppes pr??-remplies disponibles ?? l'entr??e du parking et ?? d??poser dans la boite au lettre jaune et verte situ?? le long du grillage.%0A" .
            "Le paiement se fait soit par ch??que ?? l'ordre de M.Rabus/Mme Rabus soit en esp??ces.%0AVous restez en possession des cl??s de v??tre v??hicule.%0ASi vous avez des questions n'h??sitez pas." ;
    }

    // template pour la confirmation d'une reservation
    public function messageReservation(){

        $this->Sujet = "Confirmation de v??tre r??servation";
        $this->SujetCourt = "Confirmation";
        $aujourdhui = new \DateTime();

        // Message classique
        $this->Message = $this->Message . "Je vous confirme v??tre r??servation " . $this->place() . " de parking du " .$this->Reservation->getDateArrivee()->format('d/m') . " au " .
            $this->Reservation->getDateDepart()->format('d/m') . " au tarif de " . $this->Reservation->getprix() . "???.";

        // Message avec code
        if( $this->Reservation->getDateArrivee()->diff($aujourdhui)->days < 5){
            $message = $this->Message;
            $this->MessageCode();
            $this->Message = $message . "%0A%0A" . $this->Message;

            // Dans ce cas on veut enregistrer qu'on a donne le code
            $this->Reservation->setCodeDonne = true;
        }
        else{
            $this->Message = $this->Message . "%0A%0AJe vous remercie de me recontacter par sms/mail/telephone 48h00 avant v??tre arriv??e au parking afin d'obtenir v??tre code d'acc??s, il sera ??galement valable pour v??tre retour.";
        }

        //Message avec explication du code
    }

    // template pour l'allongement d'une reservation
    public function messageAllongement(){

        $this->Sujet = "Allongement de v??tre r??servation";
        $this->SujetCourt = "Allongement";
        $this->Message = "Je vous confirme l'allongement de v??tre r??servation " . $this->place() . " de parking du " .$this->Reservation->getDateArrivee()->format('d/m') . " au " .
            $this->Reservation->getDateDepart()->format('d/m') . " au tarif de " . $this->Reservation->getprix() . "???.";
    }

    // template pour l'annulation d'une reservation
    public function messageAnnulation(){

        $this->Sujet = "Annulation de v??tre r??servation";
        $this->SujetCourt = "Annulation";
        $this->Message = "Je vous confirme l'annulation de v??tre r??servation " . $this->place() . " de parking du " .$this->Reservation->getDateArrivee()->format('d/m') . " au " .
            $this->Reservation->getDateDepart()->format('d/m') . " au tarif de " . $this->Reservation->getprix() . "???.";
    }

    // template pour proposition reservation
    public function messageSiVousVoulez(){

        $this->Sujet = "Confirmez v??tre r??servation";
        $this->SujetCourt = "Si vous voulez";

        if(!$this->NombreReservation){
            $this->messageExplication();
            $this->Message = $this->Message . "%0A%0A";
        }

        $this->Message = $this->Message . "Si vous voulez je vous confirme v??tre r??servation " . $this->place() . " de parking du " .$this->Reservation->getDateArrivee()->format('d/m') . " au " .
            $this->Reservation->getDateDepart()->format('d/m') . " au tarif de " . $this->Reservation->getprix() . "???.";

    }

    // traitement du formulaire pour les messages
    public function traitementFormulaire($formulaire, $doctrine,bool $href = true ) : array{

        if($formulaire['debut']){
            $this->Heure(2);
        }

        if($formulaire['fin']){
            $this->Heure(3);
        }

        if($formulaire['reservation']){
            $this->messageReservation();
        }

        //
        if($formulaire['code']){
            $this->messageCode();
            $this->Reservation->setCodeDonne(true);
            $entityManager = $doctrine->getManager();
            $entityManager->persist($this->Reservation);
            $entityManager->flush();

            // On envoie le mail pour le code automatiquement
            if($this->Reservation->getClient()->getEmail()){
                // Changer destinaire pour la prod
                $data = ['destinataire' => 'jules200204@gmail.com', 'sujet' => $this->getSujet() . ' : ' .$this->Reservation->getCodeAcces()->getCode(), 'template' => 'code', 'message' => $this ];
                $this->sendEmail($data);
            }
        }

        if($formulaire['explication']){
            $this->messageExplication();
        }

        // On retourne le message/ le mail

        if($Client =  $this->Reservation->getClient()->getEmail()){
            return ['message'=>$this->getMessageMail(),'type'=>'Mail'];
        }
        return ['message'=>$this->getMessageTelephone($href),'type'=>'Sms'];

    }

    // fonction pour envoyer le mail
    private function sendEmail($data){
        $email = (new TemplatedEmail())
            ->from(new Address ('reservation@parking-rue-du-moulin.fr', 'Parking Rue Du Moulin'))
            ->to($data['destinataire'])
            ->bcc(new Address('reservation@parking-rue-du-moulin.fr','Copie Mail'))
            ->subject($data['sujet'])
            ->htmlTemplate('mail/' . $data['template'] . '.html.twig' )
            ->context(['message' => ($data["message"])]);
        $this->Mailer->send($email);
    }

    public function messageIntelligent(){

        $aujourdhui = new \DateTime();

        if( $this->Reservation->getDateArrivee()->diff($aujourdhui)->days < 5 ){
            $this->messageCode();
            return ["message"=> $this->getMessageTelephone(), "sujet"=> $this->SujetCourt];
        }

        $this->messageReservation();
        return ["message"=> $this->getMessageTelephone(), "sujet"=> $this->SujetCourt];
    }


}