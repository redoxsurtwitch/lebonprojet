<?php
// Importation des classes nécessaires de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Inclusion des fichiers de la bibliothèque PHPMailer
require 'PHPMailer/src/Exception.php'; // Gestion des exceptions pour PHPMailer
require 'PHPMailer/src/PHPMailer.php'; // Classe principale de PHPMailer
require 'PHPMailer/src/SMTP.php'; // Gestion du protocole SMTP

// Récupération du paramètre 'action' depuis l'URL et conversion en entier
$action = intval($_GET['action']); 

// Récupération du paramètre 'user' depuis l'URL
$username = $_GET['user']; 

// Vérifie si l'action demandée est égale à 1 (envoi de confirmation de ticket)
if ($action == 1) {
    $mail = new PHPMailer(true); // Création d'un nouvel objet PHPMailer avec gestion des exceptions
    
    try {
        // Configuration du serveur SMTP pour l'envoi des e-mails
        $mail->isSMTP(); // Utilisation du protocole SMTP
        $mail->Host       = 'smtp.gmail.com'; // Définition du serveur SMTP
        $mail->SMTPAuth   = true; // Activation de l'authentification SMTP
        $mail->Username   = 'service2ticketing@gmail.com'; // Adresse e-mail de l'expéditeur
        $mail->Password   = 'ugyf gygb fyxv hmlb'; // ⚠ Mot de passe en dur (risque de sécurité)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Chiffrement SSL/TLS
        $mail->Port       = 465; // Utilisation du port sécurisé 465
        
        // Configuration des destinataires
        $mail->setFrom('service2ticketing@gmail.com', 'Ticketing'); // Expéditeur du mail
        $mail->addAddress('narthexgives2@gmail.com'); // Premier destinataire
        $mail->addAddress('loanbrunet13@gmail.com'); // Deuxième destinataire

        // Configuration du contenu de l'e-mail
        $mail->isHTML(true); // Activation du format HTML
        $mail->Subject = ucfirst($username) . ', votre ticket a été pris en compte'; // Sujet du mail
        
        // Définition du corps du message en HTML
        $mail->Body = '<html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; background-color: #ecf0f1; color: #333; }
            </style>
        </head>
        <body>
            <p>Bonjour, ' . $username . '</p>
            <p>Votre ticket a bien été pris en compte.</p>
        </body>
        </html>';

        // Définition du corps alternatif en texte brut (pour les clients ne supportant pas HTML)
        $mail->AltBody = "Bonjour,\nVotre ticket a bien été pris en compte.";

        // Envoi de l'e-mail
        $mail->send(); 

        // Redirection de l'utilisateur après l'envoi du mail
        header('Location: Membres/membre.php');
        exit(); // Arrête l'exécution du script

    } catch (Exception $e) {
        // Gestion des erreurs et affichage du message d'erreur
        echo "Message non envoyé. Erreur : {$mail->ErrorInfo}";
    }
}

// Vérifie si l'action demandée est égale à 2 (confirmation de création de compte)
if ($action == 2) {
    $mail = new PHPMailer(true); // Création d'une instance PHPMailer

    try {
        // Configuration du serveur SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'service2ticketing@gmail.com';
        $mail->Password   = 'ugyf gygb fyxv hmlb'; // ⚠ Problème de sécurité : mot de passe en clair
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Configuration des destinataires
        $mail->setFrom('service2ticketing@gmail.com', 'Ticketing');
        $mail->addAddress('narthexgives2@gmail.com');
        $mail->addAddress('loanbrunet13@gmail.com');

        // Objet du mail
        $mail->isHTML(true);
        $mail->Subject = ucfirst($username) . ', votre compte a bien été créé';

        // Corps HTML du mail
        $mail->Body = '<html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; background-color: #ecf0f1; color: #333; }
            </style>
        </head>
        <body>
            <p>Bonjour, ' . $username . '</p>
            <p>Votre compte a bien été créé.</p>
        </body>
        </html>';

        // Version texte brut pour les clients qui ne supportent pas HTML
        $mail->AltBody = "Bonjour,\nVotre compte a bien été créé.";

        // Envoi du mail
        $mail->send();

        // Redirection de l'utilisateur après envoi
        header('Location: login.php');
        exit(); // Arrêt du script

    } catch (Exception $e) {
        // Gestion des erreurs et affichage d'un message si l'envoi échoue
        echo "Message non envoyé. Erreur : {$mail->ErrorInfo}";
    }
}
?>
