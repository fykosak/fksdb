<?php

namespace Events\Transitions;

use Authentication\AccountManager;
use Events\Machine\BaseMachine;
use Events\Machine\Machine;
use Events\Machine\Transition;
use Events\Model\Holder\BaseHolder;
use Mail\MailTemplateFactory;
use ModelAuthToken;
use ModelEvent;
use ModelLogin;
use Nette\Diagnostics\Debugger;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Object;
use Nette\Utils\Strings;
use ORM\IModel;
use ServiceAuthToken;
use ServicePerson;

/**
 * Sends email with given template name (in standard template directory)
 * to the person that is found as the primary of the application that is
 * experienced the transition.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class MailSender extends Object {

    const BCC_PARAM = 'notifyBcc';
    const FROM_PARAM = 'notifyFrom';
    /**
     * @var string
     */
    private $filename;

    /**
     * @var IMailer
     */
    private $mailer;

    /**
     * @var MailTemplateFactory
     */
    private $mailTemplateFactory;

    /**
     * @var AccountManager
     */
    private $accountManager;

    /**
     * @var ServiceAuthToken
     */
    private $serviceAuthToken;

    /**
     * @var ServicePerson
     */
    private $servicePerson;

    function __construct($filename, IMailer $mailer, MailTemplateFactory $mailTemplateFactory, AccountManager $accountManager, ServiceAuthToken $serviceAuthToken, ServicePerson $servicePerson) {
        $this->filename = $filename;
        $this->mailer = $mailer;
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->accountManager = $accountManager;
        $this->serviceAuthToken = $serviceAuthToken;
        $this->servicePerson = $servicePerson;
    }

    public function __invoke(Transition $transition) {
        $this->send($transition);
    }

    private function send(Transition $transition) {
        $baseHolder = $transition->getBaseHolder();
        $person = $this->getPerson($baseHolder);

        if (!$person) {
            return;
        }
        $info = $person->getInfo();
        $email = $info ? $info->email : null;

        if (!$email) {
            return;
        }

        $login = $person->getLogin();
        if (!$login) {
            $login = $this->accountManager->createLogin($person);
        }

        $baseMachine = $transition->getBaseMachine();
        $message = $this->composeMessage($this->filename, $login, $baseMachine);

        $this->mailer->send($message);
    }

    private function composeMessage($filename, ModelLogin $login, BaseMachine $baseMachine) {
        $machine = $baseMachine->getMachine();
        $holder = $machine->getHolder();
        $person = $login->getPerson();
        $event = $holder->getEvent();
        $email = $person->getInfo()->email;
        $application = $holder->getPrimaryHolder()->getModel();

        $until = $this->getUntil($event);
        $token = $this->serviceAuthToken->createToken($login, ModelAuthToken::TYPE_EVENT_NOTIFY, $until, $event->getPrimary());

        // prepare and send email      
        $template = $this->mailTemplateFactory->createFromFile($filename);
        $template->token = $token->token;
        $template->person = $person;
        $template->until = $until;
        $template->event = $event;
        $template->application = $application;
        $template->holder = $holder;
        $template->machine = $machine;
        $template->baseMachine = $baseMachine;
        // TODO baseHolder


        $message = new Message();
        $message->setHtmlBody($template);
        $message->setSubject($this->getSubject($event, $application, $machine));

        $message->setFrom($holder->getParameter(self::FROM_PARAM));
        $message->addBcc($holder->getParameter(self::BCC_PARAM));
        $message->addTo($email, $person->getFullname());

        Debugger::log((string) $message->getHtmlBody()); //TODO move logging to mailer
        return $message;
    }

    private function getPerson(BaseHolder $baseHolder) {
        return $this->servicePerson->findByPrimary($baseHolder->getPersonId());
    }

    private function getSubject(ModelEvent $event, IModel $application, Machine $machine) {
        $application = Strings::truncate((string) $application, 20); //TODO extension point
        return $event->name . ': ' . $application . ' ' . mb_strtolower($machine->getPrimaryMachine()->getStateName());
    }

    private function getUntil(ModelEvent $event) {
        return $event->registration_end; //TODO extension point
    }

}
