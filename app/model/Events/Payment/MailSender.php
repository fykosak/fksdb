<?php

namespace Events\Payment;

use Mail\MailTemplateFactory;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Object;
use ServicePerson;

/**
 * Sends email with given template name (in standard template directory)
 * to the person that is found as the primary of the application that is
 * experienced the transition.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class MailSender{

    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $address;

    /**
     * @var IMailer
     */
    private $mailer;

    /**
     * @var MailTemplateFactory
     */
    private $mailTemplateFactory;

    /**
     * @var ServicePerson
     */
    private $servicePerson;

    function __construct($filename, $address, IMailer $mailer, MailTemplateFactory $mailTemplateFactory, ServicePerson $servicePerson) {
        $this->filename = $filename;
        $this->address = $address;
        $this->mailer = $mailer;
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->servicePerson = $servicePerson;
    }

    public function send() {
        $message = $this->composeMessage($this->filename);
        $this->mailer->send($message);
    }

    private function composeMessage($filename) {
        // prepare and send email
        $template = $this->mailTemplateFactory->createFromFile($filename);
        $message = new Message();

        $message->setHtmlBody($template);
        $message->setSubject($this->subject);
        $message->setFrom($this->form);
        $message->addBcc($this->bcc);
        $message->addTo($this->address);

        return $message;
    }
}
