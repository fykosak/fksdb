<?php

namespace FKSDB\Events\Transitions;

use FKSDB\Authentication\AccountManager;
use FKSDB\Events\Machine\BaseMachine;
use FKSDB\Events\Machine\Machine;
use FKSDB\Events\Machine\Transition;
use FKSDB\Events\Model\Holder\BaseHolder;
use FKSDB\Events\Model\Holder\Holder;
use FKSDB\Localization\UnsupportedLanguageException;
use FKSDB\Mail\MailTemplateFactory;
use FKSDB\Modules\PublicModule\ApplicationPresenter;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\ModelAuthToken;
use FKSDB\ORM\Models\ModelEmailMessage;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelLogin;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServiceAuthToken;
use FKSDB\ORM\Services\ServiceEmailMessage;
use FKSDB\ORM\Services\ServicePerson;
use Nette\SmartObject;
use Nette\Utils\Strings;

/**
 * Sends email with given template name (in standard template directory)
 * to the person that is found as the primary of the application that is
 * experienced the transition.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class MailSender {
    use SmartObject;

    public const BCC_PARAM = 'notifyBcc';
    public const FROM_PARAM = 'notifyFrom';

    // Adressee
    public const ADDR_SELF = 'self';
    public const ADDR_PRIMARY = 'primary';
    public const ADDR_SECONDARY = 'secondary';
    public const ADDR_ALL = '*';
    public const BCC_PREFIX = '.';

    /** @var string */
    private $filename;

    /**
     *
     * @var array|string
     */
    private $addressees;

    private MailTemplateFactory $mailTemplateFactory;

    private AccountManager $accountManager;

    private ServiceAuthToken $serviceAuthToken;

    private ServicePerson $servicePerson;

    private ServiceEmailMessage $serviceEmailMessage;

    /**
     * MailSender constructor.
     * @param string $filename
     * @param array|string $addresees
     * @param MailTemplateFactory $mailTemplateFactory
     * @param AccountManager $accountManager
     * @param ServiceAuthToken $serviceAuthToken
     * @param ServicePerson $servicePerson
     * @param ServiceEmailMessage $serviceEmailMessage
     */
    public function __construct(
        $filename,
        $addresees,
        MailTemplateFactory $mailTemplateFactory,
        AccountManager $accountManager,
        ServiceAuthToken $serviceAuthToken,
        ServicePerson $servicePerson,
        ServiceEmailMessage $serviceEmailMessage
    ) {
        $this->filename = $filename;
        $this->addressees = $addresees;
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->accountManager = $accountManager;
        $this->serviceAuthToken = $serviceAuthToken;
        $this->servicePerson = $servicePerson;
        $this->serviceEmailMessage = $serviceEmailMessage;
    }

    /**
     * @param Transition $transition
     * @param Holder $holder
     * @return void
     * @throws UnsupportedLanguageException
     */
    public function __invoke(Transition $transition, Holder $holder): void {
        $this->send($transition, $holder);
    }

    /**
     * @param Transition $transition
     * @param Holder $holder
     * @return void
     * @throws UnsupportedLanguageException
     */
    private function send(Transition $transition, Holder $holder): void {
        $personIds = $this->resolveAdressees($transition, $holder);
        $persons = $this->servicePerson->getTable()
            ->where('person.person_id', $personIds)
            ->where(':person_info.email IS NOT NULL')
            ->fetchPairs('person_id');

        $logins = [];
        /** @var ModelPerson $person */
        foreach ($persons as $person) {
            $login = $person->getLogin();
            if (!$login) {
                $login = $this->accountManager->createLogin($person);
            }
            $logins[] = $login;
        }

        foreach ($logins as $login) {
            $this->createMessage($this->filename, $login, $transition->getBaseMachine(), $holder->getBaseHolder($transition->getBaseMachine()->getName()));
        }
    }

    /**
     * @param string $filename
     * @param ModelLogin $login
     * @param BaseMachine $baseMachine
     * @param BaseHolder $baseHolder
     * @return ModelEmailMessage
     * @throws UnsupportedLanguageException
     */
    private function createMessage(string $filename, ModelLogin $login, BaseMachine $baseMachine, BaseHolder $baseHolder): ModelEmailMessage {
        $machine = $baseMachine->getMachine();

        $holder = $baseHolder->getHolder();
        $person = $login->getPerson();
        $event = $baseHolder->getEvent();
        $email = $person->getInfo()->email;
        $application = $holder->getPrimaryHolder()->getModel();

        $token = $this->createToken($login, $event, $application);

        // prepare and send email
        $templateParams = [
            'token' => $token->token,
            'person' => $person,
            'until' => $token->until,
            'event' => $event,
            'application' => $application,
            'holder' => $holder,
            'machine' => $machine,
            'baseMachine' => $baseMachine,
            'baseHolder' => $baseHolder,
            'linkArgs' => [
                '//:Public:Application:',
                [
                    'eventId' => $event->event_id,
                    'contestId' => $event->getEventType()->contest_id,
                    'at' => $token->token,
                ],
            ],
        ];
        $template = $this->mailTemplateFactory->createWithParameters($filename, null, $templateParams);

        $data = [];
        $data['text'] = (string)$template;
        $data['subject'] = $this->getSubject($event, $application, $holder, $machine);
        $data['sender'] = $holder->getParameter(self::FROM_PARAM);
        $data['reply_to'] = $holder->getParameter(self::FROM_PARAM);
        if ($this->hasBcc()) {
            $data['blind_carbon_copy'] = $holder->getParameter(self::BCC_PARAM);
        }
        $data['recipient'] = $email;
        $data['state'] = ModelEmailMessage::STATE_WAITING;
        return $this->serviceEmailMessage->createNewModel($data);

    }

    private function createToken(ModelLogin $login, ModelEvent $event, IModel $application): ModelAuthToken {
        $until = $this->getUntil($event);
        $data = ApplicationPresenter::encodeParameters($event->getPrimary(), $application->getPrimary());
        return $this->serviceAuthToken->createToken($login, ModelAuthToken::TYPE_EVENT_NOTIFY, $until, $data, true);
    }

    /**
     * @param ModelEvent $event
     * @param IModel $application
     * @param Holder $holder
     * @param Machine $machine
     * @return string
     * TODO extension point
     */
    private function getSubject(ModelEvent $event, IModel $application, Holder $holder, Machine $machine): string {
        if (in_array($event->event_type_id, [4, 5])) {
            return _('Pozvánka na soustředění');
        }
        $application = Strings::truncate((string)$application, 20);
        return $event->name . ': ' . $application . ' ' . mb_strtolower($machine->getPrimaryMachine()->getStateName($holder->getPrimaryHolder()->getModelState()));
    }

    /**
     * @param ModelEvent $event
     * @return \DateTimeInterface
     * TODO extension point
     */
    private function getUntil(ModelEvent $event): \DateTimeInterface {
        return $event->registration_end ?: $event->end;
    }

    private function hasBcc(): bool {
        return !is_array($this->addressees) && substr($this->addressees, 0, strlen(self::BCC_PREFIX)) == self::BCC_PREFIX;
    }

    private function resolveAdressees(Transition $transition, Holder $holder): array {
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
                    $names = [$transition->getBaseMachine()->getName()];
                    break;
                case self::ADDR_PRIMARY:
                    $names = [$holder->getPrimaryHolder()->getName()];
                    break;
                case self::ADDR_SECONDARY:
                    $names = [];
                    foreach ($holder->getGroupedSecondaryHolders() as $group) {
                        $names = array_merge($names, array_map(function (BaseHolder $it): string {
                            return $it->getName();
                        }, $group['holders']));
                    }
                    break;
                case self::ADDR_ALL:
                    $names = array_keys($holder->getBaseHolders());
                    break;
                default:
                    $names = [];
            }
        }

        $persons = [];
        foreach ($names as $name) {
            $personId = $holder->getBaseHolder($name)->getPersonId();
            if ($personId) {
                $persons[] = $personId;
            }
        }
        return $persons;
    }
}
