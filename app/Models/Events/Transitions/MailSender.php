<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Transitions;

use FKSDB\Models\Authentication\AccountManager;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Mail\MailTemplateFactory;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\EmailMessageModel;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\AuthTokenService;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\Transitions\Callbacks\MailCallback;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Modules\PublicModule\ApplicationPresenter;
use Fykosak\NetteORM\Exceptions\ModelException;
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

    public const BCC_PREFIX = '.';
    /**
     * @var array|string
     */
    protected $addressees;

    /**
     * MailSender constructor.
     * @param array|string $addresees
     */
    public function __construct(
        string $templateFile,
        $addresees,
        MailTemplateFactory $mailTemplateFactory,
        AccountManager $accountManager,
        AuthTokenService $authTokenService,
        EmailMessageService $emailMessageService
    ) {
        parent::__construct(
            $templateFile,
            [],
            $emailMessageService,
            $mailTemplateFactory,
            $authTokenService,
            $accountManager
        );
        $this->addressees = $addresees;
    }

    /**
     * @param BaseHolder $holder
     * @return PersonModel[]
     * @throws \ReflectionException
     */
    protected function getPersonFromHolder(ModelHolder $holder): array
    {
        return [$holder->getPerson()];
    }

    /**
     * @param BaseHolder $holder
     * @throws BadTypeException
     * @throws ModelException
     * @throws \ReflectionException
     */
    protected function createMessage(PersonModel $person, ModelHolder $holder): EmailMessageModel
    {
        $data = [];
        $data['subject'] = $this->getSubject($holder->event, $holder->getModel());
        $data['sender'] = $holder->getParameter(self::FROM_PARAM);
        $data['reply_to'] = $holder->getParameter(self::FROM_PARAM);
        if ($this->hasBcc()) {
            $data['blind_carbon_copy'] = $holder->getParameter(self::BCC_PARAM);
        }

        $data['recipient_person_id'] = $person->person_id;
        $data['text'] = $this->createMessageText($person, $holder);
        return $this->emailMessageService->addMessageToSend($data);
    }

    /**
     * @throws \ReflectionException
     */
    protected function createToken(PersonModel $person, ModelHolder $holder): AuthTokenModel
    {
        $event = $holder->getModel()->getReferencedModel(EventModel::class);
        $login = $this->resolveLogin($person);
        $data = ApplicationPresenter::encodeParameters(
            $event->getPrimary(),
            $holder->getModel()->getPrimary()
        );
        return $this->authTokenService->createToken(
            $login,
            AuthTokenModel::TYPE_EVENT_NOTIFY,
            $event->registration_end ?? $event->end,
            $data,
            true
        );
    }

    private function getSubject(EventModel $event, Model $application): string
    {
        if (in_array($event->event_type_id, [4, 5])) {
            return _('Camp invitation');
        }
        $application = Strings::truncate((string)$application, 20);
        return $event->name . ': ' . $application;
    }

    private function hasBcc(): bool
    {
        return !is_array($this->addressees)
            && substr($this->addressees, 0, strlen(self::BCC_PREFIX)) == self::BCC_PREFIX;
    }
}
