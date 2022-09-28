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

    public function __construct(
        string $templateFile,
        EmailMessageService $emailMessageService,
        MailTemplateFactory $mailTemplateFactory,
        AuthTokenService $authTokenService,
        AccountManager $accountManager
    ) {
        $this->templateFile = $templateFile;
        $this->accountManager = $accountManager;
        $this->emailMessageService = $emailMessageService;
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->accountManager = $accountManager;
        $this->authTokenService = $authTokenService;
    }


    /**
     * @throws \ReflectionException
     * @throws BadTypeException
     */
    public function __invoke(ModelHolder $holder): void
    {
        foreach ($this->getPersonsFromHolder($holder) as $person) {
            $data = $this->getData($person, $holder);
            $data['recipient_person_id'] = $person->person_id;
            $data['text'] = (string)$this->mailTemplateFactory->createWithParameters(
                $this->templateFile,
                $person->getPreferredLang(),
                [
                    'holder' => $holder,
                    'token' => $this->createToken($person, $holder),
                ]
            );
            $this->emailMessageService->addMessageToSend($data);
        }
    }

    protected function getData(PersonModel $person, ModelHolder $holder): array
    {
        return [
            'subject' => '',
            'blind_carbon_copy' => 'FYKOS <fykos@fykos.cz>',
            'sender' => 'fykos@fykos.cz',
        ];
    }

    protected function resolveLogin(PersonModel $person): LoginModel
    {
        return $person->getLogin() ?? $this->accountManager->createLogin($person);
    }

    protected function createToken(PersonModel $person, ModelHolder $holder): AuthTokenModel
    {
        return $this->authTokenService->createToken($this->resolveLogin($person), 'default', null);
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function createMessage(PersonModel $person, ModelHolder $holder): EmailMessageModel
    {
        $data = $this->emailData;
        $data['recipient_person_id'] = $person->person_id;
        $data['text'] = $this->createMessageText($person, $holder);
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
    protected function createMessageText(PersonModel $person, ModelHolder $holder): string
    {
        $token = $this->createToken($person, $holder);
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
     * @return PersonModel[]
     * @throws \ReflectionException
     * @throws BadTypeException
     */
    protected function getPersonsFromHolder(ModelHolder $holder): array
    {
        $person = $holder->getModel()->getReferencedModel(PersonModel::class);
        if (is_null($person)) {
            throw new BadTypeException(PersonModel::class, $person);
        }
        return [$person];
    }
}
