<?php

$expediteur = 'reservation@parking-rue-du-moulin.fr';
$entete = array("From" => "Parking-rue-du-moulin<$expediteur>", "Reply-To" => $expediteur, "X-Mailer" => "PHP/" . phpversion(), "MIME-Version" => "1.0", "Content-type" => "text/html;charset=UTF-8");

$header = "
    <!DOCTYPE html>
    <html lang='fr'>
    <head>
        <meta http-equiv='Content-Type' content='text/html' charset='UTF-8' />
        <title>" . $sujet . " Parking-Rue-Du-Moulin</title>
        <style>
        
            body{
                font-family: Calibri;
                text-align: center;
            }
            
            table{
                width: 100%;
                table-layout: fixed;
                border: 2px solid black;
                border-collapse: collapse;
            }
            
            table th,td{
                border: 2px solid black;
                padding: 1rem;  
            }
            article{
                text-align: center;
            }

            article h1{
                font-size: 2rem;
            }
            
            footer{
                padding: 0.5rem;
                text-align: center;
            }

        </style>
    </head>
    
    <body>
        <main>";
