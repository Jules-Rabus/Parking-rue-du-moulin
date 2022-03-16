<?php

namespace App\Entity;

use App\Entity\Reservation;

class Message
{

    private string $Message;

    private string $Debut;

    private string $Fin;

    private Reservation $Reservation;

    private string $Sujet;

    private int $NombreReservation;

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

    public function Heure(){

        $soleil = date_sun_info((new \DateTime())->getTimestamp(), 49.375, 2.1935);
        $heure = new \DateTime();
        $midi = (new \DateTime())->setTime(12,0,0);
        $apresMidi = (new \DateTime())->setTime(16,0,0);
        $soir = (new \DateTime())->setTime(19,0,0);
        $jour = (new \DateTime())->format('l');

        if ($heure > $soleil || $heure > $soir ){
            $debut = "Bonsoir";
            $fin = "Bonne soirée";
        }
        else{
            $debut = "Bonjour";
            $fin = "Bonne journée";
        }

        if( $heure < $apresMidi && $heure > $midi  ){
            $fin = "Bon après midi";
        }
        if( $heure > $apresMidi && $heure < $soir && $heure < $soleil){
            $fin = "Bonne fin de journée";
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

        $this->Debut = $debut . ",%0a%0a";
        $this->Fin = "%0a%0a" . $fin . " à vous,%0aCordialement M. Rabus";
    }

    public function getMessageMail() : string{

        $this->Heure();

        $message = str_replace(' ','%20',($this->Debut . $this->Message . $this->Fin));

        return 'href=mailto:' . $this->contact() . '?subject=' . $this->Sujet . '&body=' . $message ;

    }

    public function getMessageTelephone() : string{

        $this->Heure();

        $message = str_replace(' ','%20',($this->Debut . $this->Message . $this->Fin));

        return 'href=sms:' . $this->contact() . ';?&body=' . $message ;
    }

    public function contact() : string{

        if($Client =  $this->Reservation->getClient()){
            return $Client->getEmail();
        }
        return $this->Reservation->getTelephone();

    }

    public function MessageCode(){

        $this->Message = "Votre code d'accès sera le : " . $this->Reservation->getCodeAcces()->getCode();

        if($this->NombreReservation < 2 ){
            $this->Message = $this->Message . "%0A%0AJe vous rappelle également que le paiement se fait soit par chèque à l'ordre de M.Rabus/Mme Rabus ou en espèces à l'arrivée sur le parking via des enveloppes pré-remplies.";
        }

    }

    public function Place() : string{

        if( $nombrePlace = $this->Reservation->getNombrePlace() > 1){
            return "de " . $nombrePlace .  " places";
        }
        return "d'une place" ;

    }

    public function MessageExplication(){
        $this->Message = "Le parking se situe entre le 17 et le 19 rue du moulin à Tillé (portail noir) à 650 mètres à pied de l'aéroport.%0AL'accès au parking se fait via un portail motorisé à digicode. " .
            "Je vous remercie de me recontacter par sms/mail/telephone 48h00 avant votre arrivée au parking afin d'obtenir votre code d'accès, il sera également valable pour votre retour.%0A" .
            "Le paiement s'éffectue à votre arrivée au moyen d'enveloppes pré-remplies disponibles à l'entrée du parking et à déposer dans la boite au lettre jaune et verte situé le long du grillage.%0A" .
            "Le paiement se fait soit par chèque à l'ordre de M.Rabus/Mme Rabus soit en espèces.%0AVous restez en possession des clés de votre véhicule.%0ASi vous avez des questions n'hésitez pas." ;
    }

    public function MessageReservation(){

        if(!$this->NombreReservation){
            $this->MessageExplication();
            $this->Message = $this->Message . "%0A%0A";
        }

        $this->Message = $this->Message . "Je vous confirme votre réservation " . $this->Place() . " de parking du " .$this->Reservation->getDateArrivee()->format('d/m') . " au " .
            $this->Reservation->getDateDepart()->format('d/m') . " au tarif de " . $this->Reservation->prix() . "€";

    }

    public function MessageAllongement(){
        $this->Message = "Je vous confirme l'allongement de votre réservation " . $this->Place() . " de parking du " .$this->Reservation->getDateArrivee()->format('d/m') . " au " .
            $this->Reservation->getDateDepart()->format('d/m') . " au tarif de " . $this->Reservation->prix() . "€";
    }

    public function MessageAnnulation(){
        $this->Message = "Je vous confirme l'annulation de votre réservation " . $this->Place() . " de parking du " .$this->Reservation->getDateArrivee()->format('d/m') . " au " .
            $this->Reservation->getDateDepart()->format('d/m') . " au tarif de " . $this->Reservation->prix() . "€";
    }


}