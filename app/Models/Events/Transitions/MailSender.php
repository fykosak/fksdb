<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Transitions;

use FKSDB\Models\Authentication\AccountManager;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Mail\MailTemplateFactory;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\AuthTokenService;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\Transitions\Callbacks\MailCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use FKSDB\Modules\Core\Language;
use FKSDB\Modules\PublicModule\ApplicationPresenter;
use Fykosak\NetteORM\Model;
use Nette\Utils\Strings;

/**
 * Sends email with given template name (in standard template directory)
 * to the person that is found as the primary of the application that is
 * experienced the transition.
 * @phpstan-extends MailCallback<ParticipantHolder>
 */
class MailSender extends MailCallback
{
    private string $templateFile;

    public function __construct(
        string $templateFile,
        MailTemplateFactory $mailTemplateFactory,
        AccountManager $accountManager,
        AuthTokenService $authTokenService,
        EmailMessageService $emailMessageService
    ) {
        parent::__construct(
            $emailMessageService,
            $mailTemplateFactory,
            $authTokenService,
            $accountManager
        );
        $this->templateFile = $templateFile;
    }

    /**
     * @param ParticipantHolder $holder
     * @phpstan-return PersonModel[]
     */
    protected function getPersonsFromHolder(ModelHolder $holder): array
    {
        return [$holder->getModel()->person];
    }

    /**
     * @param ParticipantHolder $holder
     * @throws \ReflectionException
     */
    protected function createToken(PersonModel $person, ModelHolder $holder): AuthTokenModel
    {
        $event = $holder->getModel()->getReferencedModel(EventModel::class);
        return $this->authTokenService->createToken(
            $this->resolveLogin($person),
            AuthTokenType::from(AuthTokenType::EVENT_NOTIFY),
            $event->registration_end ?? $event->end,
            ApplicationPresenter::encodeParameters($event->getPrimary(), $holder->getModel()->getPrimary()),
            true
        );
    }

    /**
     * @param ParticipantHolder $holder
     * @phpstan-return array{
     *     blind_carbon_copy:string|null,
     *     subject:string,
     *     sender:string,
     *     reply_to:string,
     * }
     */
    protected function getData(ModelHolder $holder): array
    {
        return [
            'blind_carbon_copy' => $holder->getModel()->event->getParameter('notifyBcc') ?? null,
            'subject' => $this->getSubject($holder->getModel()->event, $holder->getModel()),
            'sender' => $holder->getModel()->event->getParameter('notifyFrom'),
            'reply_to' => $holder->getModel()->event->getParameter('notifyFrom'),
        ];
    }

    /**
     * @param ParticipantHolder $holder
     * @throws \ReflectionException
     * @throws BadTypeException
     */
    protected function createMessageText(ModelHolder $holder, PersonModel $person): string
    {
        $token = $this->createToken($person, $holder);
        return $this->mailTemplateFactory->renderWithParameters(
            $this->getTemplatePath($holder),
            Language::tryFrom($person->getPreferredLang()),
            [
                'person' => $person,
                'token' => $token,
                'holder' => $holder,
                'linkArgs' => $this->createLinkArgs($holder, $token),
            ]
        );
    }

    /**
     * @param ParticipantHolder $holder
     * @throws \ReflectionException
     * @phpstan-return array{string,array<string,scalar>}
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

    protected function getSubject(EventModel $event, Model $application): string
    {
        if (in_array($event->event_type_id, [4, 5])) {
            return _('Camp invitation');
        }
        $application = Strings::truncate((string)$application, 20);
        return $event->name . ': ' . $application;
    }

    /**
     * @param ParticipantHolder $holder
     */
    protected function getTemplatePath(ModelHolder $holder): string
    {
        return $this->templateFile;
    }
}
