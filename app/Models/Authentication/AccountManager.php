<?php

declare(strict_types=1);

namespace FKSDB\Models\Authentication;

use FKSDB\Models\Authentication\Exceptions\RecoveryExistsException;
use FKSDB\Models\Authentication\Exceptions\RecoveryNotImplementedException;
use FKSDB\Models\Email\TemplateFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Mail\TemplateFactory;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\ORM\Models\EmailMessageTopic;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\AuthTokenService;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\ORM\Services\LoginService;
use FKSDB\Modules\Core\Language;
use Nette\Application\BadRequestException;
use Nette\SmartObject;
use Nette\Utils\DateTime;

class AccountManager
{
    use SmartObject;

    private string $invitationExpiration;
    private string $recoveryExpiration;
    private string $emailFrom;
    private TemplateFactory $mailTemplateFactory;
    private LoginService $loginService;
    private AuthTokenService $authTokenService;
    private EmailMessageService $emailMessageService;

    public function __construct(
        string $invitationExpiration,
        string $recoveryExpiration,
        string $emailFrom,
        TemplateFactory $mailTemplateFactory,
        LoginService $loginService,
        AuthTokenService $authTokenService,
        EmailMessageService $emailMessageService
    ) {
        $this->invitationExpiration = $invitationExpiration;
        $this->recoveryExpiration = $recoveryExpiration;
        $this->emailFrom = $emailFrom;
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->loginService = $loginService;
        $this->authTokenService = $authTokenService;
        $this->emailMessageService = $emailMessageService;
    }

    /**
     * Creates login and invites user to set up the account.
     * @throws BadTypeException
     * @throws \Exception
     */
    public function sendLoginWithInvitation(PersonModel $person, string $email, Language $lang): LoginModel
    {
        $login = $this->loginService->createLogin($person);

        $until = DateTime::from($this->invitationExpiration);
        $token = $this->authTokenService->createToken(
            $login,
            AuthTokenType::from(AuthTokenType::INITIAL_LOGIN),
            $until
        );
        $data = $this->mailTemplateFactory->renderWithParameters(
            __DIR__ . '/loginInvitation.latte',
            [
                'token' => $token,
                'person' => $person,
                'email' => $email,
                'until' => $until,
                'lang' => $person->getPreferredLang() ?? $lang->value,
            ],
            Language::from($person->getPreferredLang() ?? $lang->value)
        );
        $data['sender'] = $this->emailFrom;
        $data['recipient_person_id'] = $person->person_id;
        $data['topic'] = EmailMessageTopic::from(EmailMessageTopic::Internal);
        $data['lang'] = $lang;
        $this->emailMessageService->addMessageToSend($data);
        return $login;
    }

    /**
     * @throws BadTypeException
     * @throws \Exception
     */
    public function sendRecovery(LoginModel $login, Language $lang): void
    {
        if (!$login->person_id) {
            throw new RecoveryNotImplementedException();
        }
        /** @var AuthTokenModel|null $token */
        $token = $login->getActiveTokens(AuthTokenType::from(AuthTokenType::RECOVERY))->fetch();
        if ($token) {
            throw new RecoveryExistsException();
        }

        $until = DateTime::from($this->recoveryExpiration);
        $token = $this->authTokenService->createToken($login, AuthTokenType::from(AuthTokenType::RECOVERY), $until);

        $person = $login->person;
        if (!$person) {
            throw new BadRequestException();
        }
        $data = $this->mailTemplateFactory->renderWithParameters(
            __DIR__ . '/recovery.latte',
            [
                'token' => $token,
                'person' => $person,
                'lang' => $lang->value,
            ],
            $lang
        );
        $data['sender'] = $this->emailFrom;
        $data['recipient_person_id'] = $login->person_id;
        $data['topic'] = EmailMessageTopic::from(EmailMessageTopic::Internal);
        $data['lang'] = $lang;

        $this->emailMessageService->addMessageToSend($data);
    }
}
