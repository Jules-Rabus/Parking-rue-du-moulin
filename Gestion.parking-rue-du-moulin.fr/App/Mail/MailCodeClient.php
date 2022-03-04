<?php
$sujet = "Votre code d'accès au parking : $code";
require 'MailHeader.php';
require 'MailFooter.php';

$message_html = $header . "
    <article>
        <h1>Votre code d'accès : " . $code . "</h1>
        <table>
            <thead>
                <tr>
                    <th>Date d'arrivée</th>
                    <th>Date de départ</th>
                    <th>Nombre de véhicule</th>
                    <th>Tarif de la réservation</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td> " . $reservation['date'] . "</td>
                    <td> " . $reservation['datef'] . "</td>
                    <td> " . $reservation['place'] . "</td>
                    <td> " . $tarif . " €</td>
                </tr>
            </tbody>
        </table>
    </article>
    <article>
        <p>Adresse du parking : Entre le 17 et le 19 rue du moulin à Tillé (portail noir).</p>

    </article>" . $footer;

