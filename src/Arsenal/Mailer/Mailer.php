<?php
namespace Arsenal\Mailer;
require 'src/Swift/swift_required.php';

class Mailer extends \Swift_Mailer
{
    public function __construct($host, $port, $security, $username, $password)
    {
        $transport = \Swift_SmtpTransport::newInstance($host, $port, $security)
            ->setUsername($username)
            ->setPassword($password);
        parent::__construct($transport);
    }
    
    public function createMail()
    {
        return new Mail($this);
    }
}