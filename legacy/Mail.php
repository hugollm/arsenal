<?php

require 'src/SwiftMailer/swift_required.php';

class Mail extends Swift_Message
{
    private static $mailer = null;
    
    public static function create()
    {
        return new self;
    }
    
    public function send(array &$failures = array())
    {
        $mailer = self::getMailer();
        return $mailer->send($this, $failures);
    }
    
    private static function getMailer()
    {
        if( ! self::$mailer)
        {
            $transport = Swift_SmtpTransport::newInstance(Config::get('mail.host'), Config::get('mail.port'), Config::get('mail.security'))
                ->setUsername(Config::get('mail.username'))
                ->setPassword(Config::get('mail.password'));
            self::$mailer = new Swift_Mailer($transport);
        }
        return self::$mailer;
    }
}