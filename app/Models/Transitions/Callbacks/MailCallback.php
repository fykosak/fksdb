<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks;

use FKSDB\Models\Authentication\AccountManager;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\Mail\MailTemplateFactory;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\AuthTokenService;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\Transitions\Holder\ModelHolder;

class MailCallback implements TransitionCallback
{

    protected EmailMessageService $emailMessageService;
    protected MailTemplateFactory $mailTemplateFactory;
    protected AccountManager $accountManager;
    protected AuthTokenService $authTokenService;

    public function __construct(
        EmailMessageService $emailMessageService,
        MailTemplateFactory $mailTemplateFactory,
        AuthTokenService $authTokenService,
        AccountManager $accountManager
    ) {
        $this->emailMessageService = $emailMessageService;
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->accountManager = $accountManager;
        $this->authTokenService = $authTokenService;
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     * @throws NotImplementedException
     */
    public function __invoke(ModelHolder $holder, ...$args): void
    {
        foreach ($this->getPersonsFromHolder($holder) as $person) {
            $data = $this->getData($holder);
            $data['recipient_person_id'] = $person->person_id;
            $data['text'] = (string)$this->mailTemplateFactory->createWithParameters(
                $this->getTemplatePath($holder),
                $person->getPreferredLang(),
                [
                    'holder' => $holder,
                    'token' => $this->createToken($person, $holder),
                ]
            );
            $this->emailMessageService->addMessageToSend($data);
        }
    }

    /**
     * @throws NotImplementedException
     */
    protected function getTemplatePath(ModelHolder $holder): string
    {
        throw new NotImplementedException();
    }

    protected function getData(ModelHolder $holder): array
    {
        return [
            'subject' => 'FYKOS',
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
