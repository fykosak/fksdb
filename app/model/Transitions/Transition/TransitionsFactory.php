<?php

namespace FKSDB\Transitions;

use Authorization\EventAuthorizator;
use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelPayment;
use FKSDB\ORM\ModelPerson;
use Mail\MailTemplateFactory;
use Nette\DateTime;
use Nette\InvalidStateException;
use Nette\Localization\ITranslator;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Security\IResource;
use Nette\Security\User;

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
    /**
     * @var User
     */
    private $user;
    /**
     * @var ITranslator
     */
    private $translator;

    /**
     * TransitionsFactory constructor.
     * @param IMailer $mailer
     * @param MailTemplateFactory $mailTemplateFactory
     * @param EventAuthorizator $eventAuthorizator
     * @param User $user
     */
    public function __construct(IMailer $mailer, MailTemplateFactory $mailTemplateFactory, EventAuthorizator $eventAuthorizator, User $user, ITranslator $translator) {
        $this->mailer = $mailer;
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->eventAuthorizator = $eventAuthorizator;
        $this->user = $user;
        $this->translator = $translator;
    }

    /**
     * @param string|null $fromState
     * @param string $toState
     * @param string $label
     * @return Transition
     */
    public function createTransition(string $fromState = null, string $toState, string $label) {
        $transition = new Transition($fromState, $toState, $label);

        return $transition;
    }

    /**
     * @param string $templateFile
     * @param $options
     * @return \Closure
     */
    public function createMailCallback(string $templateFile, $options): \Closure {
        $template = $this->mailTemplateFactory->createFromFile($templateFile);
        $template->setTranslator($this->translator);

        $message = new Message();
        $message->setSubject($options->subject);
        $message->setFrom($options->from);
        $message->addBcc($options->bcc);

        //  $message->addAttachment()

        return function (ModelPayment $model) use ($message, $template) {

            $template->model = $model;

            $message->addTo($model->getPerson()->getInfo()->email);
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

    /**
     * @param DateTime $from
     * @param DateTime $to
     * @return bool
     */
    public function getConditionDateBetween(DateTime $from, DateTime $to): bool {
        return $this->getConditionDateFrom($from) && $this->getConditionDateTo($to);
    }

    /**
     * @param DateTime $from
     * @return bool
     */
    public function getConditionDateFrom(DateTime $from): bool {
        return \time() >= $from->getTimestamp();
    }

    /**
     * @param DateTime $to
     * @return bool
     */
    public function getConditionDateTo(DateTime $to): bool {
        return \time() <= $to->getTimestamp();
    }

    /**
     * @param ModelPerson $ownerPerson
     * @return bool
     * @throws InvalidStateException
     */
    public function getConditionOwnerAssertion(ModelPerson $ownerPerson): bool {
        if (!$this->user->isLoggedIn()) {
            throw new InvalidStateException('Expecting logged user.');
        }
        /**
         * @var $loggedPerson ModelPerson
         */
        $loggedPerson = $this->user->getIdentity()->getPerson();
        return $loggedPerson->person_id === $ownerPerson->person_id;
    }
}

