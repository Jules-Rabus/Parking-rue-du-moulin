<?php
$sujet = "Code a envoyer : $count";
require 'MailHeader.php';

$message_html = $header . "
    <article class='message'>
        <h2>Nombre de code a envoyer : " . $count . "</h2>
        <h2>Nombre de mail envoyé : " . $count_mail . "</h2>

    </article>";

