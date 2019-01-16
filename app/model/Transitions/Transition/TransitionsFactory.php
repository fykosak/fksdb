<?php

namespace FKSDB\Transitions;

use Authorization\EventAuthorizator;
use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelPerson;
use FKSDB\Transitions\Conditions\DateBetween;
use Mail\MailTemplateFactory;
use Nette\DateTime;
use Nette\InvalidStateException;
use Nette\Localization\ITranslator;
use Nette\Mail\IMailer;
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
     * @param ITranslator $translator
     */
    public function __construct(IMailer $mailer, MailTemplateFactory $mailTemplateFactory, EventAuthorizator $eventAuthorizator, User $user, ITranslator $translator) {
        $this->mailer = $mailer;
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->eventAuthorizator = $eventAuthorizator;
        $this->user = $user;
        $this->translator = $translator;
    }

    /**
     * @param string $fromState
     * @param string $toState
     * @param string $label
     * @return Transition
     */
    public function createTransition(string $fromState, string $toState, string $label): Transition {
        $transition = new Transition($fromState, $toState, $label);
        return $transition;
    }

    /**
     * @param string $templateFile
     * @param \Closure $optionsCallback
     * @return \Closure
     */
    public function createMailCallback(string $templateFile, \Closure $optionsCallback): \Closure {
        $template = $this->mailTemplateFactory->createFromFile($templateFile);
        $template->setTranslator($this->translator);
        return function (IStateModel $model) use ($optionsCallback, $template) {
            $message = $optionsCallback($model);

            $template->model = $model;

            $message->setHtmlBody($template);
            $this->mailer->send($message);
        };
    }
    /* conditions */
    /**
     * @param ModelEvent $event
     * @param IResource|string $resource
     * @param string $privilege
     * @return bool
     */
    public function getConditionEventRole(ModelEvent $event, $resource, string $privilege): bool {
        return $this->eventAuthorizator->isAllowed($resource, $privilege, $event);
    }

    /**
     * @param DateTime $from
     * @param DateTime $to
     * @return bool
     */
    public function getConditionDateBetween(DateTime $from, DateTime $to): bool {
        return (new DateBetween($from, $to))();
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

