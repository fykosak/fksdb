<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks;

use FKSDB\Models\Authentication\AccountManager;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Mail\MailTemplateFactory;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\EmailMessageModel;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\AuthTokenService;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Transition\Statements\Statement;
use Nette\SmartObject;

class MailCallback implements Statement
{
    use SmartObject;

    protected EmailMessageService $emailMessageService;
    protected MailTemplateFactory $mailTemplateFactory;
    protected AccountManager $accountManager;
    protected AuthTokenService $authTokenService;
    protected string $templateFile;
    protected array $emailData;

    public function __construct(
        string $templateFile,
        array $emailData,
        EmailMessageService $emailMessageService,
        MailTemplateFactory $mailTemplateFactory,
        AuthTokenService $authTokenService,
        AccountManager $accountManager
    ) {
        $this->templateFile = $templateFile;
        $this->emailData = $emailData;
        $this->accountManager = $accountManager;
        $this->emailMessageService = $emailMessageService;
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->authTokenService = $authTokenService;
    }


    /**
     * @throws \ReflectionException
     * @throws BadTypeException
     */
    public function __invoke(ModelHolder $holder): void
    {
        foreach ($this->getPersonFromHolder($holder) as $person) {
            $login = $person->getLogin();
            if (!$login) {
                $login = $this->accountManager->createLogin($person);
            }
            $this->createMessage($login, $person, $holder);
        }
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function createMessage(LoginModel $login, PersonModel $person, ModelHolder $holder): EmailMessageModel
    {
        $data = $this->emailData;

        $data['recipient_person_id'] = $person->person_id;
        $data['text'] = $this->createMessageText($login, $person, $holder);
        return $this->emailMessageService->addMessageToSend($data);
    }

    /**
     * @throws \ReflectionException
     */
    public function createLinkArgs(ModelHolder $holder, AuthTokenModel $token): array
    {
        $event = $holder->getModel()->getReferencedModel(EventModel::class);
        return [
            '//:Public:Application:',
            [
                'eventId' => $event->event_id,
                'contestId' => $event->event_type->contest_id,
                'at' => $token->token,
            ],
        ];
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function createMessageText(LoginModel $login, PersonModel $person, ModelHolder $holder): string
    {

        $token = $this->createToken($login, $holder);
        return (string)$this->mailTemplateFactory->createWithParameters(
            $this->templateFile,
            $person->getPreferredLang(),
            [
                'tokenModel' => $token,
                'holder' => $holder,
                'linkArgs' => $this->createLinkArgs($holder, $token),
            ]
        );
    }

    /**
     * @throws \ReflectionException
     */
    protected function createToken(LoginModel $login, ModelHolder $holder): AuthTokenModel
    {
        $event = $holder->getModel()->getReferencedModel(EventModel::class);
        return $this->authTokenService->createToken(
            $login,
            AuthTokenModel::TYPE_EVENT_NOTIFY,
            $event->registration_end ?? $event->end,
            null,
            true
        );
    }

    /**
     * @return PersonModel[]
     * @throws \ReflectionException
     * @throws BadTypeException
     */
    protected function getPersonFromHolder(ModelHolder $holder): array
    {
        $person = $holder->getModel()->getReferencedModel(PersonModel::class);
        if (is_null($person)) {
            throw new BadTypeException(PersonModel::class, $person);
        }
        return [$person];
    }
}
