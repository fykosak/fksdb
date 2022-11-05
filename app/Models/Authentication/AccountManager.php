<?php

declare(strict_types=1);

namespace FKSDB\Models\Authentication;

use FKSDB\Models\Authentication\Exceptions\ChangeInProgressException;
use FKSDB\Models\Authentication\Exceptions\RecoveryExistsException;
use FKSDB\Models\Authentication\Exceptions\RecoveryNotImplementedException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Mail\MailTemplateFactory;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\AuthTokenService;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\ORM\Services\LoginService;
use FKSDB\Modules\Core\Language;
use Nette\SmartObject;
use Nette\Utils\DateTime;
use Tracy\Debugger;

class AccountManager
{
    use SmartObject;

    public function __construct(
        private readonly string $invitationExpiration,
        private readonly string $recoveryExpiration,
        private readonly string $emailFrom,
        private readonly MailTemplateFactory $mailTemplateFactory,
        private readonly LoginService $loginService,
        private readonly AuthTokenService $authTokenService,
        private readonly EmailMessageService $emailMessageService
    ) {
    }

    /**
     * Creates login and invites user to set up the account.
     * @throws BadTypeException
     * @throws \Exception
     */
    public function sendLoginWithInvitation(PersonModel $person, string $email, string $lang): LoginModel
    {
        $login = $this->createLogin($person);

        $until = DateTime::from($this->invitationExpiration);
        $token = $this->authTokenService->createToken($login, AuthTokenType::InitialLogin, $until);
        $data = [];
        $data['text'] = $this->mailTemplateFactory->renderLoginInvitation(
            [
                'token' => $token->token,
                'person' => $person,
                'email' => $email,
                'until' => $until,
                'lang' => $person->getPreferredLang() ?? $lang,
            ]
        );
        $data['subject'] = _('Create an account');
        $data['sender'] = $this->emailFrom;
        $data['recipient_person_id'] = $person->person_id;
        $this->emailMessageService->addMessageToSend($data);
        return $login;
    }

    /**
     * @throws BadTypeException
     * @throws \Exception
     */
    public function sendRecovery(LoginModel $login, string $lang): void
    {
        if (!$login->person_id) {
            throw new RecoveryNotImplementedException();
        }
        $token = $login->getActiveTokens(AuthTokenType::Recovery)->fetch();
        if ($token) {
            throw new RecoveryExistsException();
        }

        $until = DateTime::from($this->recoveryExpiration);
        $token = $this->authTokenService->createToken($login, AuthTokenType::Recovery, $until);
        $data = [];
        $data['text'] = $this->mailTemplateFactory->renderPasswordRecovery([
            'token' => $token,
            'person' => $login->person,
            'lang' => $lang,
        ]);
        $data['subject'] = _('Password recovery');
        $data['sender'] = $this->emailFrom;
        $data['recipient_person_id'] = $login->person_id;

        $this->emailMessageService->addMessageToSend($data);
    }

    /**
     * @throws BadTypeException
     * @throws ChangeInProgressException
     */
    public function sendChangeEmail(PersonModel $person, string $newEmail, Language $lang): void
    {
        Debugger::log(
            sprintf(
                'person %d (%s) with old email "%s" ask change to %s',
                $person->person_id,
                $person->getFullName(),
                $person->getInfo()->email,
                $newEmail
            ),
            'email-change'
        );
        $login = $person->getLogin();
        if (!$login) {
            $this->createLogin($person);
        }
        $token = $login->getActiveTokens(AuthTokenType::ChangeEmail)->fetch();
        if ($token) {
            throw new ChangeInProgressException();
        }
        $token = $this->authTokenService->createToken(
            $login,
            AuthTokenType::ChangeEmail,
            (new \DateTime())->modify('+20 minutes'),
            $newEmail
        );
        $oldData = [
            'text' => $this->mailTemplateFactory->renderChangePasswordOld(
                ['lang' => $lang, 'person' => $person, 'newEmail' => $newEmail,]
            ),
            'sender' => $this->emailFrom,
            'subject' => _('Change email'),
            'recipient' => $person->getInfo()->email,
        ];
        $newData = [
            'text' => $this->mailTemplateFactory->renderChangePasswordNew(
                ['lang' => $lang, 'person' => $person, 'newEmail' => $newEmail, 'token' => $token,]
            ),
            'sender' => $this->emailFrom,
            'subject' => _('Confirm you email'),
            'recipient' => $newEmail,
        ];
        $this->emailMessageService->addMessageToSend($oldData);
        $this->emailMessageService->addMessageToSend($newData);
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
            $hash = $login->calculateHash($password);
            $this->loginService->storeModel(['hash' => $hash], $login);
        }
        return $login;
    }
}
