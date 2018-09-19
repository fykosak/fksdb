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
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Object;
use Nette\Utils\Strings;
use ORM\IModel;
use PublicModule\ApplicationPresenter;
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

    // Adressee
    const ADDR_SELF = 'self';
    const ADDR_PRIMARY = 'primary';
    const ADDR_SECONDARY = 'secondary';
    const ADDR_ALL = '*';
    const BCC_PREFIX = '.';

    /**
     * @var string
     */
    private $filename;

    /**
     *
     * @var array
     */
    private $addressees;

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

    function __construct($filename, $addresees, IMailer $mailer, MailTemplateFactory $mailTemplateFactory, AccountManager $accountManager, ServiceAuthToken $serviceAuthToken, ServicePerson $servicePerson) {
        $this->filename = $filename;
        $this->addressees = $addresees;
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
        $personIds = $this->resolveAdressee($transition);
        $persons = $this->servicePerson->getTable()
            ->where('person.person_id', $personIds)
            ->where('person_info:email IS NOT NULL')
            ->fetchPairs('person_id');

        $logins = array();
        foreach ($persons as $person) {
            $login = $person->getLogin();
            if (!$login) {
                $login = $this->accountManager->createLogin($person);
            }
            $logins[] = $login;
        }

        foreach ($logins as $login) {
            $message = $this->composeMessage($this->filename, $login, $transition->getBaseMachine());
            $this->mailer->send($message);
        }
    }

    private function composeMessage($filename, ModelLogin $login, BaseMachine $baseMachine) {
        $machine = $baseMachine->getMachine();
        $holder = $machine->getHolder();
        $baseHolder = $holder[$baseMachine->getName()];
        $person = $login->getPerson();
        $event = $baseHolder->getEvent();
        $email = $person->getInfo()->email;
        $application = $holder->getPrimaryHolder()->getModel();

        $token = $this->createToken($login, $event, $application);
        $until = $token->until;

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
        $template->baseHolder = $baseHolder;


        $message = new Message();
        $message->setHtmlBody($template);
        $message->setSubject($this->getSubject($event, $application, $machine));

        $message->setFrom($holder->getParameter(self::FROM_PARAM));
        if ($this->hasBcc()) {
            $message->addBcc($holder->getParameter(self::BCC_PARAM));
        }
        $message->addTo($email, $person->getFullname());

        return $message;
    }

    private function createToken(ModelLogin $login, ModelEvent $event, IModel $application) {
        $until = $this->getUntil($event);
        $data = ApplicationPresenter::encodeParameters($event->getPrimary(), $application->getPrimary());
        $token = $this->serviceAuthToken->createToken($login, ModelAuthToken::TYPE_EVENT_NOTIFY, $until, $data, true);
        return $token;
    }

    private function getPerson(BaseHolder $baseHolder) {
        return $this->servicePerson->findByPrimary($baseHolder->getPersonId());
    }

    private function getSubject(ModelEvent $event, IModel $application, Machine $machine) {
        $application = Strings::truncate((string)$application, 20); //TODO extension point
        return $event->name . ': ' . $application . ' ' . mb_strtolower($machine->getPrimaryMachine()->getStateName());
    }

    private function getUntil(ModelEvent $event) {
        return $event->registration_end ?: $event->end; //TODO extension point
    }

    private function hasBcc() {
        return !is_array($this->addressees) && substr($this->addressees, 0, strlen(self::BCC_PREFIX)) == self::BCC_PREFIX;
    }

    private function resolveAdressee(Transition $transition) {
        $holder = $transition->getBaseHolder()->getHolder();
        if (is_array($this->addressees)) {
            $names = $this->addressees;
        } else {
            if ($this->hasBcc()) {
                $addressees = substr($this->addressees, strlen(self::BCC_PREFIX));
            } else {
                $addressees = $this->addressees;
            }
            switch ($addressees) {
                case self::ADDR_SELF:
                    $names = array($transition->getBaseHolder()->getName());
                    break;
                case self::ADDR_PRIMARY:
                    $names = array($holder->getPrimaryHolder()->getName());
                    break;
                case self::ADDR_SECONDARY:
                    $names = array();
                    foreach ($holder->getGroupedSecondaryHolders() as $group) {
                        $names = array_merge($names, array_map(function ($it) {
                            return $it->getName();
                        }, $group->holders));
                    }
                    break;
                case self::ADDR_ALL:
                    $names = array_keys(iterator_to_array($transition->getBaseHolder()->getHolder()));
                    break;
                default:
                    $names = array();
            }
        }


        $persons = array();
        foreach ($names as $name) {
            $personId = $holder[$name]->getPersonId();
            if ($personId) {
                $persons[] = $personId;
            }
        }

        return $persons;
    }

}
