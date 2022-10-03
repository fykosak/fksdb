<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Transitions;

use FKSDB\Models\Authentication\AccountManager;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Mail\MailTemplateFactory;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\AuthTokenService;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\Transitions\Callbacks\MailCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Modules\PublicModule\ApplicationPresenter;
use Fykosak\NetteORM\Model;
use Nette\Utils\Strings;

/**
 * Sends email with given template name (in standard template directory)
 * to the person that is found as the primary of the application that is
 * experienced the transition.
 */
class MailSender extends MailCallback
{
    public const BCC_PARAM = 'notifyBcc';
    public const FROM_PARAM = 'notifyFrom';

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
     * @param BaseHolder $holder
     * @return PersonModel[]
     * @throws \ReflectionException
     */
    protected function getPersonsFromHolder(ModelHolder $holder): array
    {
        return [$holder->getPerson()];
    }

    /**
     * @throws \ReflectionException
     */
    protected function createToken(PersonModel $person, ModelHolder $holder): AuthTokenModel
    {
        $event = $holder->getModel()->getReferencedModel(EventModel::class);
        return $this->authTokenService->createToken(
            $this->resolveLogin($person),
            AuthTokenModel::TYPE_EVENT_NOTIFY,
            $event->registration_end ?? $event->end,
            ApplicationPresenter::encodeParameters($event->getPrimary(), $holder->getModel()->getPrimary()),
            true
        );
    }

    /**
     * @param BaseHolder $holder
     */
    protected function getData(ModelHolder $holder): array
    {
        return [
            'blind_carbon_copy' => $holder->getParameter(self::BCC_PARAM) ?? null,
            'subject' => $this->getSubject($holder->event, $holder->getModel()),
            'sender' => $holder->getParameter(self::FROM_PARAM),
            'reply_to' => $holder->getParameter(self::FROM_PARAM),
        ];
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function createMessageText(ModelHolder $holder, PersonModel $person): string
    {
        $token = $this->createToken($person, $holder);
        return (string)$this->mailTemplateFactory->createWithParameters(
            $this->getTemplatePath($holder),
            $person->getPreferredLang(),
            [
                'person' => $person,
                'token' => $token,
                'holder' => $holder,
                'linkArgs' => $this->createLinkArgs($holder, $token),
            ]
        );
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

    protected function getSubject(EventModel $event, Model $application): string
    {
        if (in_array($event->event_type_id, [4, 5])) {
            return _('Camp invitation');
        }
        $application = Strings::truncate((string)$application, 20);
        return $event->name . ': ' . $application;
    }

    protected function getTemplatePath(ModelHolder $holder): string
    {
        return $this->templateFile;
    }
}
