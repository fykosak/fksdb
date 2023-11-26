<?php

declare(strict_types=1);

namespace FKSDB\Models\Mail\FOF;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Mail\MailTemplateFactory;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\AuthTokenService;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\ORM\Services\LoginService;
use FKSDB\Models\Transitions\Holder\TeamHolder;
use FKSDB\Models\Transitions\Statement;
use FKSDB\Modules\Core\Language;
use Nette\DI\Container;

/**
 * @phpstan-implements Statement<void,TeamHolder>
 * @phpstan-import-type TRenderedData from MailTemplateFactory
 */
abstract class InfoEmail implements Statement
{
    protected EmailMessageService $emailMessageService;
    protected MailTemplateFactory $mailTemplateFactory;
    protected AuthTokenService $authTokenService;
    protected LoginService $loginService;

    public function __construct(Container $container)
    {
        $container->callInjects($this);
    }

    public function inject(
        EmailMessageService $emailMessageService,
        MailTemplateFactory $mailTemplateFactory,
        AuthTokenService $authTokenService,
        LoginService $loginService
    ): void {
        $this->emailMessageService = $emailMessageService;
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->authTokenService = $authTokenService;
        $this->loginService = $loginService;
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
            $data = array_merge($this->getData($holder), $this->createMessageText($holder, $person));
            $data['recipient_person_id'] = $person->person_id;
            $this->emailMessageService->addMessageToSend($data);
        }
    }

    /**
     * @throws BadTypeException
     * @phpstan-return TRenderedData
     */
    protected function createMessageText(TeamHolder $holder, PersonModel $person): array
    {
        return $this->mailTemplateFactory->renderWithParameters2(
            $this->getTemplatePath($holder),
            [
                'person' => $person,
                'holder' => $holder,
                'token' => $this->createToken($person, $holder),
            ],
            Language::tryFrom($person->getPreferredLang())
        );
    }
    final protected function resolveLogin(PersonModel $person): LoginModel
    {
        return $person->getLogin() ?? $this->loginService->createLogin($person);
    }

    protected function createToken(PersonModel $person, TeamHolder $holder): ?AuthTokenModel
    {
        return null;
    }

    abstract protected function getTemplatePath(TeamHolder $holder): string;

    /**
     * @phpstan-return array{
     *     blind_carbon_copy?:string,
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
