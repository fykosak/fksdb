<?php

namespace FKSDB\Transitions;

use Authorization\EventAuthorizator;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\Transitions\Callbacks\EmailCallback;
use Mail\MailTemplateFactory;
use Nette\InvalidStateException;
use Nette\Localization\ITranslator;
use Nette\Mail\IMailer;
use Nette\Security\User;

/**
 * Class TransitionsFactory
 * @package FKSDB\Transitions
 */
class TransitionsFactory {
    /**
     * @var IMailer
     */
    private $mailer;

    /**
     * @return IMailer
     */
    public function getMailer(): IMailer {
        return $this->mailer;
    }

    /**
     * @return MailTemplateFactory
     */
    public function getMailTemplateFactory(): MailTemplateFactory {
        return $this->mailTemplateFactory;
    }

    /**
     * @return EventAuthorizator
     */
    public function getEventAuthorizator(): EventAuthorizator {
        return $this->eventAuthorizator;
    }

    /**
     * @return User
     */
    public function getUser(): User {
        return $this->user;
    }

    /**
     * @return ITranslator
     */
    public function getTranslator(): ITranslator {
        return $this->translator;
    }

    /**
     * @var MailTemplateFactory
     */
    private $mailTemplateFactory;
    /**
     * @var EventAuthorizator
     */
    public $eventAuthorizator;
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
     * @param string $templateFile
     * @param callable $optionsCallback
     * @return callable
     */
    public function createMailCallback(string $templateFile, callable $optionsCallback): callable {
        return new EmailCallback($optionsCallback, $templateFile, $this->getTranslator(), $this->getMailer(), $this->getMailTemplateFactory());
    }
    /* conditions */

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
         * @var ModelPerson $loggedPerson
         */
        $loggedPerson = $this->user->getIdentity()->getPerson();
        return $loggedPerson->person_id === $ownerPerson->person_id;
    }
}

