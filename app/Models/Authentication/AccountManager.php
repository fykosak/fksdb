<?php

declare(strict_types=1);

namespace FKSDB\Models\Authentication;

use FKSDB\Models\Authentication\Exceptions\RecoveryExistsException;
use FKSDB\Models\Authentication\Exceptions\RecoveryNotImplementedException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\AuthTokenService;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\ORM\Services\LoginService;
use FKSDB\Models\Mail\MailTemplateFactory;
use Nette\SmartObject;
use Nette\Utils\DateTime;

class AccountManager
{
    use SmartObject;

    private LoginService $loginService;
    private AuthTokenService $authTokenService;
    private string $invitationExpiration;
    private string $recoveryExpiration;
    private string $emailFrom;
    private EmailMessageService $emailMessageService;
    private MailTemplateFactory $mailTemplateFactory;

    public function __construct(
        string $invitationExpiration,
        string $recoveryExpiration,
        string $emailFrom,
        MailTemplateFactory $mailTemplateFactory,
        LoginService $loginService,
        AuthTokenService $authTokenService,
        EmailMessageService $emailMessageService
    ) {
        $this->invitationExpiration = $invitationExpiration;
        $this->recoveryExpiration = $recoveryExpiration;
        $this->emailFrom = $emailFrom;
        $this->loginService = $loginService;
        $this->authTokenService = $authTokenService;
        $this->emailMessageService = $emailMessageService;
        $this->mailTemplateFactory = $mailTemplateFactory;
    }

    /**
     * Creates login and invites user to set up the account.
     * @throws BadTypeException
     * @throws \Exception
     */
    public function createLoginWithInvitation(PersonModel $person, string $email, string $lang): LoginModel
    {
        $login = $this->createLogin($person);

        $until = DateTime::from($this->invitationExpiration);
        $token = $this->authTokenService->createToken($login, AuthTokenModel::TYPE_INITIAL_LOGIN, $until);
        $data = [];
        $data['text'] = (string)$this->mailTemplateFactory->createLoginInvitation(
            $person->getPreferredLang() ?? $lang,
            [
                'token' => $token->token,
                'person' => $person,
                'email' => $email,
                'until' => $until,
            ]
        );
        $data['subject'] = _('Create an account');
        $data['sender'] = $this->emailFrom;
        $data['recipient'] = $email;
        $this->emailMessageService->addMessageToSend($data);
        return $login;
    }

    /**
     * @throws BadTypeException
     * @throws \Exception
     */
    public function sendRecovery(LoginModel $login, string $lang): void
    {
        $person = $login->person;
        $recoveryAddress = $person ? $person->getInfo()->email : null;
        if (!$recoveryAddress) {
            throw new RecoveryNotImplementedException();
        }
        $token = $login->getTokens(AuthTokenModel::TYPE_RECOVERY)
            ->where('until > ?', new DateTime())->fetch();
        if ($token) {
            throw new RecoveryExistsException();
        }

        $until = DateTime::from($this->recoveryExpiration);
        $token = $this->authTokenService->createToken($login, AuthTokenModel::TYPE_RECOVERY, $until);
        $templateParams = [
            'token' => $token->token,
            'login' => $login,
            'until' => $until,
        ];
        $data = [];
        $data['text'] = (string)$this->mailTemplateFactory->createPasswordRecovery($lang, $templateParams);
        $data['subject'] = _('Password recovery');
        $data['sender'] = $this->emailFrom;
        $data['recipient'] = $recoveryAddress;

        $this->emailMessageService->addMessageToSend($data);
    }

    public function cancelRecovery(LoginModel $login): void
    {
        $login->getTokens(AuthTokenModel::TYPE_RECOVERY)->delete();
    }

    final public function createLogin(PersonModel $person, ?string $login = null, ?string $password = null): LoginModel
    {
        /** @var LoginModel $login */
        $login = $this->loginService->storeModel([
            'person_id' => $person->person_id,
            'login' => $login,
            'active' => 1,
        ]);

        /* Must be done after login_id is allocated. */
        if ($password) {
            $hash = $login->createHash($password);
            $this->loginService->storeModel(['hash' => $hash], $login);
        }
        return $login;
    }
}
