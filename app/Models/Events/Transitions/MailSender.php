<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Transitions;

use FKSDB\Models\Authentication\AccountManager;
use FKSDB\Models\Events\Machine\BaseMachine;
use FKSDB\Models\Events\Machine\Machine;
use FKSDB\Models\Events\Machine\Transition;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EmailMessageState;
use Fykosak\NetteORM\Exceptions\ModelException;
use FKSDB\Models\Mail\MailTemplateFactory;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\EmailMessageModel;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Services\AuthTokenService;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Modules\PublicModule\ApplicationPresenter;
use Fykosak\NetteORM\Model;
use Nette\SmartObject;
use Nette\Utils\Strings;

/**
 * Sends email with given template name (in standard template directory)
 * to the person that is found as the primary of the application that is
 * experienced the transition.
 */
class MailSender
{
    use SmartObject;

    public const BCC_PARAM = 'notifyBcc';
    public const FROM_PARAM = 'notifyFrom';
    // Addressee
    public const ADDR_SELF = 'self';
    public const ADDR_PRIMARY = 'primary';
    public const ADDR_SECONDARY = 'secondary';
    public const ADDR_ALL = '*';
    public const BCC_PREFIX = '.';
    private string $filename;
    /**
     * @var array|string
     */
    private $addressees;
    private MailTemplateFactory $mailTemplateFactory;
    private AccountManager $accountManager;
    private AuthTokenService $authTokenService;
    private PersonService $personService;
    private EmailMessageService $emailMessageService;

    /**
     * MailSender constructor.
     * @param array|string $addresees
     */
    public function __construct(
        string $filename,
        $addresees,
        MailTemplateFactory $mailTemplateFactory,
        AccountManager $accountManager,
        AuthTokenService $authTokenService,
        PersonService $personService,
        EmailMessageService $emailMessageService
    ) {
        $this->filename = $filename;
        $this->addressees = $addresees;
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->accountManager = $accountManager;
        $this->authTokenService = $authTokenService;
        $this->personService = $personService;
        $this->emailMessageService = $emailMessageService;
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    public function __invoke(Transition $transition, Holder $holder): void
    {
        $this->send($transition, $holder);
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    private function send(Transition $transition, Holder $holder): void
    {
        $personIds = $this->resolveAdressees($transition, $holder);
        $persons = $this->personService->getTable()
            ->where('person.person_id', $personIds)
            ->where(':person_info.email IS NOT NULL')
            ->fetchPairs('person_id');

        $logins = [];
        foreach ($persons as $person) {
            $login = $person->getLogin();
            if (!$login) {
                $login = $this->accountManager->createLogin($person);
            }
            $logins[] = $login;
        }

        foreach ($logins as $login) {
            $this->createMessage(
                $login,
                $transition->getBaseMachine(),
                $holder->getBaseHolder($transition->getBaseMachine()->getName())
            );
        }
    }

    /**
     * @throws BadTypeException
     * @throws ModelException
     */
    private function createMessage(
        LoginModel $login,
        BaseMachine $baseMachine,
        BaseHolder $baseHolder
    ): EmailMessageModel {
        $machine = $baseMachine->getMachine();

        $holder = $baseHolder->holder;
        $person = $login->person;
        $event = $baseHolder->event;
        $email = $person->getInfo()->email;
        $application = $holder->primaryHolder->getModel2();

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
                    'contestId' => $event->event_type->contest_id,
                    'at' => $token->token,
                ],
            ],
        ];
        $template = $this->mailTemplateFactory->createWithParameters($this->filename, null, $templateParams);

        $data = [];
        $data['text'] = (string)$template;
        $data['subject'] = $this->getSubject($event, $application, $holder, $machine);
        $data['sender'] = $holder->getParameter(self::FROM_PARAM);
        $data['reply_to'] = $holder->getParameter(self::FROM_PARAM);
        if ($this->hasBcc()) {
            $data['blind_carbon_copy'] = $holder->getParameter(self::BCC_PARAM);
        }
        $data['recipient'] = $email;
        $data['state'] = EmailMessageState::WAITING;
        return $this->emailMessageService->storeModel($data);
    }

    private function createToken(LoginModel $login, EventModel $event, Model $application): AuthTokenModel
    {
        $until = $this->getUntil($event);
        $data = ApplicationPresenter::encodeParameters($event->getPrimary(), $application->getPrimary());
        return $this->authTokenService->createToken($login, AuthTokenModel::TYPE_EVENT_NOTIFY, $until, $data, true);
    }

    private function getSubject(EventModel $event, Model $application, Holder $holder, Machine $machine): string
    {
        if (in_array($event->event_type_id, [4, 5])) {
            return _('Camp invitation');
        }
        $application = Strings::truncate((string)$application, 20);
        return $event->name . ': ' . $application;
        //state in subject: . ' ' .
        // mb_strtolower($machine->getPrimaryMachine()->getStateName($holder->getPrimaryHolder()->getModelState()))
    }

    private function getUntil(EventModel $event): \DateTimeInterface
    {
        return $event->registration_end ?? $event->end;
    }

    private function hasBcc(): bool
    {
        return !is_array($this->addressees)
            && substr($this->addressees, 0, strlen(self::BCC_PREFIX)) == self::BCC_PREFIX;
    }

    /**
     * @return int[]
     * @throws \ReflectionException
     * @throws \ReflectionException
     */
    private function resolveAdressees(Transition $transition, Holder $holder): array
    {
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
                    $names = [$holder->primaryHolder->name];
                    break;
                case self::ADDR_SECONDARY:
                    $names = [];
                    foreach ($holder->getGroupedSecondaryHolders() as $group) {
                        $names = array_merge(
                            $names,
                            array_map(fn(BaseHolder $it): string => $it->name, $group['holders'])
                        );
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
            $person = $holder->getBaseHolder((string)$name)->getPerson();
            if ($person) {
                $persons[] = $person->person_id;
            }
        }
        return $persons;
    }
}
