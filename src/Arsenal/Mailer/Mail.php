<?php
namespace Arsenal\Mailer;

class Mail extends \Swift_Message
{
    private $mailer;
    
    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
        parent::__construct();
    }
    
    public function send(array &$failures = array())
    {
        return $this->mailer->send($this, $failures);
    }
}