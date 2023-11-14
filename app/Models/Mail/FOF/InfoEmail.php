<?php

declare(strict_types=1);

namespace FKSDB\Models\Mail\FOF;

use FKSDB\Models\Authentication\AccountManager;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Mail\MailTemplateFactory;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\AuthTokenService;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\Transitions\Holder\TeamHolder;
use FKSDB\Models\Transitions\Statement;
use FKSDB\Modules\Core\Language;
use Nette\DI\Container;

/**
 * @phpstan-implements Statement<void,TeamHolder>
 */
abstract class InfoEmail implements Statement
{
    protected EmailMessageService $emailMessageService;
    protected MailTemplateFactory $mailTemplateFactory;
    protected AccountManager $accountManager;
    protected AuthTokenService $authTokenService;

    public function __construct(Container $container)
    {
        $container->callInjects($this);
    }

    public function inject(
        EmailMessageService $emailMessageService,
        MailTemplateFactory $mailTemplateFactory,
        AuthTokenService $authTokenService,
        AccountManager $accountManager
    ): void {
        $this->emailMessageService = $emailMessageService;
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->accountManager = $accountManager;
        $this->authTokenService = $authTokenService;
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    public function __invoke(...$args): void
    {
        /**
         * @var TeamHolder $holder
         */
        [$holder] = $args;
        foreach ($this->getPersons($holder) as $person) {
            $data = $this->getData($holder);
            $data['recipient_person_id'] = $person->person_id;
            $data['text'] = $this->createMessageText($holder, $person);
            $this->emailMessageService->addMessageToSend($data);
        }
    }

    /**
     * @throws BadTypeException
     */
    protected function createMessageText(TeamHolder $holder, PersonModel $person): string
    {
        return $this->mailTemplateFactory->renderWithParameters(
            $this->getTemplatePath($holder),
            Language::tryFrom($person->getPreferredLang()),
            [
                'person' => $person,
                'holder' => $holder,
                'token' => $this->createToken($person, $holder),
            ]
        );
    }

    protected function createToken(PersonModel $person, TeamHolder $holder): ?AuthTokenModel
    {
        return null;
    }

    abstract protected function getTemplatePath(TeamHolder $holder): string;

    /**
     * @phpstan-return array{
     *     blind_carbon_copy?:string,
     *     subject:string,
     *     sender:string,
     *     reply_to?:string,
     * }
     */
    abstract protected function getData(TeamHolder $holder): array;

    /**
     * @return PersonModel[]
     * @throws \ReflectionException
     * @throws BadTypeException
     */
    abstract protected function getPersons(TeamHolder $holder): array;
}
