<?php

namespace Utility;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Mail
{
//    public static $host = 'smtp.gmail.com';

    protected static function getMailer()
    {
        static $mailer = null;

        if ($mailer === null) {
            $mailer = new PHPMailer();
            $mailer->IsSMTP();
            $mailer->isHTML(true);
            $mailer->SMTPAuth = true;
            $mailer->Host = 'smtp.gmail.com';
            $mailer->setFrom('haykokalipsis@gmail.com', 'Karen');
            $mailer->Username = 'haykokalipsis@gmail.com';
            $mailer->Password = '4elsea4ever';
            $mailer->SMTPSecure = 'tls'; //ssl or TLS
            $mailer->Port = 587; // 465 for ssl, or 587 for TLS
        }

        return $mailer;
    }

    public static function send($to, $subject, $body, $altBody = null)
    {
        $mailer = static::getMailer();
        $mailer->Subject = $subject;
        $mailer->Body = $body;
        $mailer->AltBody = $altBody;
        $mailer->addAddress($to);

        $mailer->send();
    }
}