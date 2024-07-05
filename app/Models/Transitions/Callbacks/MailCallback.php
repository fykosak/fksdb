<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Callbacks;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Mail\TemplateFactory;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\EmailMessageTopic;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\AuthTokenService;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\ORM\Services\LoginService;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Statement;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Modules\Core\Language;
use Nette\DI\Container;

/**
 * @phpstan-import-type TRenderedData from TemplateFactory
 * @phpstan-template THolder of ModelHolder
 * @implements Statement<void,THolder|Transition<THolder>>
 */
abstract class MailCallback implements Statement
{
    protected EmailMessageService $emailMessageService;
    protected TemplateFactory $mailTemplateFactory;
    protected AuthTokenService $authTokenService;
    protected LoginService $loginService;

    public function __construct(Container $container)
    {
        $container->callInjects($this);
    }

    public function inject(
        EmailMessageService $emailMessageService,
        TemplateFactory $mailTemplateFactory,
        AuthTokenService $authTokenService,
        LoginService $loginService
    ): void {
        $this->emailMessageService = $emailMessageService;
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->loginService = $loginService;
        $this->authTokenService = $authTokenService;
    }

    /**
     * @phpstan-param THolder|Transition<THolder> $args
     * @throws \ReflectionException
     * @throws BadTypeException
     */
    public function __invoke(...$args): void
    {
        /**
         * @phpstan-var THolder $holder
         * @phpstan-var Transition<THolder> $transition
         */
        [$holder, $transition] = $args;
        foreach ($this->getPersons($holder) as $person) {
            $data = array_merge(
                $this->getData($holder),
                $this->createMessageText($holder, $transition, $person)
            );
            $data['recipient_person_id'] = $person->person_id;
            $this->emailMessageService->addMessageToSend($data);
        }
    }

    /**
     * @throws BadTypeException
     * @phpstan-param THolder $holder
     * @phpstan-param Transition<THolder> $transition
     * @phpstan-return TRenderedData
     */
    protected function createMessageText(ModelHolder $holder, Transition $transition, PersonModel $person): array
    {
        return $this->mailTemplateFactory->renderWithParameters(
            $this->getTemplatePath($holder, $transition),
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

    /**
     * @phpstan-param THolder $holder
     */
    protected function createToken(PersonModel $person, ModelHolder $holder): ?AuthTokenModel
    {
        return null;
    }

    /**
     * @phpstan-return PersonModel[]
     * @throws \ReflectionException
     * @throws BadTypeException
     * @phpstan-param THolder $holder
     */
    protected function getPersons(ModelHolder $holder): array
    {
        $person = $holder->getModel()->getReferencedModel(PersonModel::class);
        if (is_null($person)) {
            throw new BadTypeException(PersonModel::class, $person);
        }
        return [$person];
    }

    /**
     * @phpstan-param THolder $holder
     * @phpstan-param Transition<THolder> $transition
     */
    abstract protected function getTemplatePath(ModelHolder $holder, Transition $transition): string;

    /**
     * @phpstan-param THolder $holder
     * @phpstan-return array{
     *     blind_carbon_copy?:string,
     *     sender:string,
     *     reply_to?:string,
     *     topic:EmailMessageTopic,
     *     lang:Language,
     * }
     */
    abstract protected function getData(ModelHolder $holder): array;

    /**
     * @template TStaticHolder of ModelHolder
     * @phpstan-param  Transition<TStaticHolder> $transition
     */
    public static function resolveLayoutName(Transition $transition): string
    {
        return $transition->source->value . '->' . $transition->target->value;
    }
}
