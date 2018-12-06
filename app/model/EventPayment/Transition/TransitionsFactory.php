<?php

namespace FKSDB\EventPayment\Transition;

use Authorization\EventAuthorizator;
use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelEventPayment;
use Mail\MailTemplateFactory;
use Nette\DateTime;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Security\IResource;

class TransitionsFactory {
    /**
     * @var IMailer
     */
    private $mailer;
    /**
     * @var MailTemplateFactory
     */
    private $mailTemplateFactory;
    /**
     * @var EventAuthorizator
     */
    private $eventAuthorizator;

    public function __construct(IMailer $mailer, MailTemplateFactory $mailTemplateFactory, EventAuthorizator $eventAuthorizator) {
        $this->mailer = $mailer;
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->eventAuthorizator = $eventAuthorizator;
    }

    public function createTransition(string $fromState = null, string $toState, string $label) {
        $transition = new Transition($fromState, $toState, $label);

        return $transition;
    }


    public function createMailCallback(string $templateFile, string $address, $options): \Closure {
        $template = $this->mailTemplateFactory->createFromFile($templateFile);
        $message = new Message();

        $message->setSubject($options->subject);
        $message->setFrom($options->from);
        $message->addBcc($options->bcc);
        $message->addTo($address);
        //  $message->addAttachment()

        return function (ModelEventPayment $model) use ($message, $template) {
            $template->model = $model;
            $message->setHtmlBody($template);
            $this->mailer->send($message);
        };
    }
    /* conditions */
    /**
     * @param ModelEvent $event
     * @param IResource $resource
     * @param string $privilege
     * @return bool
     */
    public function getConditionEventRole(ModelEvent $event, IResource $resource, string $privilege): bool {
        return $this->eventAuthorizator->isAllowed($resource, $privilege, $event);
    }

    public function getConditionDateBetween(DateTime $from, DateTime $to): bool {
        return $this->getConditionDateFrom($from) && $this->getConditionDateTo($to);
    }

    public function getConditionDateFrom(DateTime $from): bool {
        return \time() >= $from->getTimestamp();
    }

    public function getConditionDateTo(DateTime $to): bool {
        return \time() <= $to->getTimestamp();
    }
}

