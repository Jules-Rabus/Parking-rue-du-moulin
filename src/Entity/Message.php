<?php

namespace App\Entity;


class Message
{

    private string $Message;

    private string $Debut;

    private string $Fin;

    private string $Contact;

    private string $Sujet;

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
     * @return string
     */
    public function getContact(): string
    {
        return $this->Contact;
    }

    /**
     * @param string $Contact
     */
    public function setContact(string $Contact): void
    {
        $this->Contact = $Contact;
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

    public function heure(){

        $soleil = date_sun_info((new \DateTime())->getTimestamp(), 49.375, 2.1935);
        $heure = new \DateTime();
        $midi = (new \DateTime())->setTime(12,0,0);
        $apresMidi = (new \DateTime())->setTime(16,0,0);
        $soir = (new \DateTime())->setTime(19,0,0);
        $jour = (new \DateTime())->format('l');

        if ($heure > $soleil || $heure > $soir ){
            $debut = 'Bonsoir';
            $fin = 'Bonne soirée';
        }
        else{
            $debut = 'Bonjour';
            $fin = 'Bonne journée';
        }

        if( $heure < $apresMidi && $heure > $midi  ){
            $fin = 'Bon après midi';
        }
        if( $heure > $apresMidi && $heure < $soir && $heure < $soleil){
            $fin = 'Bonne fin de journée';
        }
        if ($jour == 'Friday' && ($heure > $soleil && $heure > $soir)){
            $fin = 'Bon week-end';
        }
        if ($jour == 'Sunday' && $heure < $midi){
            $fin = 'Bon dimanche';
        }
        if ($jour == 'Monday' && $heure > $midi && !($heure < $soleil || $heure < $soir)){
            $fin = 'Bonne semaine';
        }

        $this->Debut = $debut . ",\r";
        $this->Fin = "\r" . $fin . " à vous,\r";
    }

    public function messageMail() : string{

        $this->heure();

        $message = $this->Debut . $this->Message . $this->Fin;

        return "class ='mail' href='mailto:" . $this->Contact . "?subject=" . $this->Sujet . "&body=" . $message . "'";

    }

    public function messageTelephone() : string{

        $this->heure();

        $message = $this->Debut . $this->Message . $this->Fin;

        return "class ='tel' href='sms:" . $this->Contact . "?body=" . $message . "'";
    }


}