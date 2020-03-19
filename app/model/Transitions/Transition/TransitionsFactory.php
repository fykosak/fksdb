<?php

namespace FKSDB\Transitions;

use Authorization\EventAuthorizator;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServiceEmailMessage;
use Mail\MailTemplateFactory;
use Nette\InvalidStateException;
use Nette\Localization\ITranslator;
use Nette\Mail\IMailer;
use Nette\Security\User;
use Nette\Templating\Template;

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
     * @var ServiceEmailMessage
     */
    private $serviceEmailMessage;

    /**
     * TransitionsFactory constructor.
     * @param ServiceEmailMessage $serviceEmailMessage
     * @param IMailer $mailer
     * @param MailTemplateFactory $mailTemplateFactory
     * @param EventAuthorizator $eventAuthorizator
     * @param User $user
     * @param ITranslator $translator
     */
    public function __construct(ServiceEmailMessage $serviceEmailMessage, IMailer $mailer, MailTemplateFactory $mailTemplateFactory, EventAuthorizator $eventAuthorizator, User $user, ITranslator $translator) {
        $this->mailer = $mailer;
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->eventAuthorizator = $eventAuthorizator;
        $this->user = $user;
        $this->translator = $translator;
        $this->serviceEmailMessage = $serviceEmailMessage;
    }

    /**
     * @param string $templateFile
     * @param string|null $lang
     * @param array $data
     * @return Template
     */
    public function createEmailTemplate(string $templateFile, string $lang = null, array $data = []): Template {
        return $this->mailTemplateFactory->createWithParameters($templateFile, $lang, $data);
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

