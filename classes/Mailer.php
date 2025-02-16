<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'vendor/autoload.php';
require_once 'config/mail.php';

class Mailer {
    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        
        // Configuration du serveur SMTP
        $this->mailer->isSMTP();
        $this->mailer->Host = SMTP_HOST;
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = SMTP_USERNAME;
        $this->mailer->Password = SMTP_PASSWORD;
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = SMTP_PORT;
        
        // Configuration de l'expéditeur
        $this->mailer->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        
        // Configuration générale
        $this->mailer->CharSet = 'UTF-8';
        $this->mailer->isHTML(true);
    }

    public function sendPasswordReset($email, $token) {
        try {
            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password.php?token=" . $token;
            
            $this->mailer->addAddress($email);
            $this->mailer->Subject = 'Réinitialisation de votre mot de passe';
            
            // Corps du message en HTML
            $this->mailer->Body = "
                <h2>Réinitialisation de votre mot de passe</h2>
                <p>Vous avez demandé la réinitialisation de votre mot de passe.</p>
                <p>Cliquez sur le lien ci-dessous pour définir un nouveau mot de passe :</p>
                <p><a href='{$resetLink}'>{$resetLink}</a></p>
                <p>Ce lien est valable pendant 1 heure.</p>
                <p>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.</p>
                <p>Cordialement,<br>L'équipe Portfolio</p>
            ";
            
            // Version texte pour les clients mail qui ne supportent pas l'HTML
            $this->mailer->AltBody = "
                Réinitialisation de votre mot de passe
                
                Vous avez demandé la réinitialisation de votre mot de passe.
                
                Copiez et collez le lien suivant dans votre navigateur pour définir un nouveau mot de passe :
                {$resetLink}
                
                Ce lien est valable pendant 1 heure.
                
                Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.
                
                Cordialement,
                L'équipe Portfolio
            ";
            
            $this->mailer->send();
            return ['success' => true];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "L'email n'a pas pu être envoyé. Erreur : {$this->mailer->ErrorInfo}"
            ];
        }
    }
} 