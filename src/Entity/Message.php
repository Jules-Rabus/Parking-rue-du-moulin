<?php

namespace App\Entity;


class Message
{

    private string $Message;

    private string $Debut;

    private string $Fin;


    public function heure() : string{

        $soleil = date_sun_info((new \DateTime())->getTimestamp(), 49.375, 2.1935);
        $heure = (new \DateTime());
        $midi = (new \DateTime())->setTime(12,0,0);
        $apresMidi = (new \DateTime())->setTime(16,0,0);
        $soir = (new \DateTime())->setTime(19,0,0);

        dd($soleil,$heure,$midi,$apresMidi,$soir);



        if ($heure > $soleil || $heure > $soir ){
            $debut = 'Bonsoir';
            $fin = 'Bonne soirée';
        }
        else{
            $debut = 'Bonjour';
            $fin = 'Bonne journée';
        }

        if( $heure < $aprem && $heure > $midi  ){
            $fin = 'Bon après midi';
        }
        if( $heure > $aprem && $heure < $soir && $heure < $soleil){
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

        return array($debut,$fin,$soleil);
    }


}