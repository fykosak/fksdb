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
use FKSDB\Models\ORM\Services\PersonInfoService;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use Nette\SmartObject;
use Nette\Utils\DateTime;
use Tracy\Debugger;

class AccountManager
{
    use SmartObject;

    private string $invitationExpiration;
    private string $recoveryExpiration;
    private string $emailFrom;
    private MailTemplateFactory $mailTemplateFactory;
    private LoginService $loginService;
    private AuthTokenService $authTokenService;
    private EmailMessageService $emailMessageService;
    private TokenAuthenticator $tokenAuthenticator;
    private PersonInfoService $personInfoService;

    public function __construct(
        string $invitationExpiration,
        string $recoveryExpiration,
        string $emailFrom,
        MailTemplateFactory $mailTemplateFactory,
        LoginService $loginService,
        AuthTokenService $authTokenService,
        EmailMessageService $emailMessageService,
        TokenAuthenticator $tokenAuthenticator,
        PersonInfoService $personInfoService
    ) {
        $this->invitationExpiration = $invitationExpiration;
        $this->recoveryExpiration = $recoveryExpiration;
        $this->emailFrom = $emailFrom;
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->loginService = $loginService;
        $this->authTokenService = $authTokenService;
        $this->emailMessageService = $emailMessageService;
        $this->tokenAuthenticator = $tokenAuthenticator;
        $this->personInfoService = $personInfoService;
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
        $token = $this->authTokenService->createToken(
            $login,
            AuthTokenType::tryFrom(AuthTokenType::INITIAL_LOGIN),
            $until
        );
        $data = [];
        $data['text'] = $this->mailTemplateFactory->renderLoginInvitation(
            [
                'token' => $token,
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
        $token = $login->getActiveTokens(AuthTokenType::tryFrom(AuthTokenType::RECOVERY))->fetch();
        if ($token) {
            throw new RecoveryExistsException();
        }

        $until = DateTime::from($this->recoveryExpiration);
        $token = $this->authTokenService->createToken($login, AuthTokenType::tryFrom(AuthTokenType::RECOVERY), $until);
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
        self::logEmailChange($person, $newEmail, true);
        $login = $person->getLogin();
        if (!$login) {
            $this->createLogin($person);
        }
        $token = $login->getActiveTokens(AuthTokenType::tryFrom(AuthTokenType::CHANGE_EMAIL))->fetch();
        if ($token) {
            throw new ChangeInProgressException();
        }
        $token = $this->authTokenService->createToken(
            $login,
            AuthTokenType::tryFrom(AuthTokenType::CHANGE_EMAIL),
            (new \DateTime())->modify('+20 minutes'),
            $newEmail
        );
        $oldData = [
            'text' => $this->mailTemplateFactory->renderChangeEmailOld(
                ['lang' => $lang, 'person' => $person, 'newEmail' => $newEmail,]
            ),
            'sender' => $this->emailFrom,
            'subject' => _('Change of email'),
            'recipient' => $person->getInfo()->email,
        ];
        $newData = [
            'text' => $this->mailTemplateFactory->renderChangeEmailNew(
                ['lang' => $lang, 'person' => $person, 'newEmail' => $newEmail, 'token' => $token,]
            ),
            'sender' => $this->emailFrom,
            'subject' => _('Confirm your email'),
            'recipient' => $newEmail,
        ];
        $this->emailMessageService->addMessageToSend($oldData);
        $this->emailMessageService->addMessageToSend($newData);
    }

    public function handleChangeEmail(PersonModel $person, Logger $logger): void
    {
        if (
            !$person->getLogin()->getActiveTokens(AuthTokenType::tryFrom(AuthTokenType::CHANGE_EMAIL))->fetch()
            || !$this->tokenAuthenticator->isAuthenticatedByToken(AuthTokenType::tryFrom(AuthTokenType::CHANGE_EMAIL))
        ) {
            $logger->log(new Message(_('Invalid token'), Message::LVL_ERROR));
            // toto ma vypíčíť že nieje žiadny token na zmenu aktívny.
            //Možné príčiny: neskoro kliknutie na link; nebolo o zmenu vôbec požiuadané a nejak sa dostal sem.
            return;
        }
        try {
            $newEmail = $this->tokenAuthenticator->getTokenData();
            self::logEmailChange($person, $newEmail, false);
            $this->personInfoService->storeModel([
                'email' => $this->tokenAuthenticator->getTokenData(),
            ], $person->getInfo());
            $logger->log(new Message(_('Email has been changed.'), Message::LVL_SUCCESS));
            $this->tokenAuthenticator->disposeAuthToken();
        } catch (\Throwable $exception) {
            $logger->log(new Message(_('Some error occurred! Please contact system admins.'), Message::LVL_ERROR));
        }
    }

    private static function logEmailChange(PersonModel $person, string $newEmail, bool $request): void
    {
        Debugger::log(
            sprintf(
                $request
                    ? 'request: person %d (%s) old: "%s" new: "%s"'
                    : 'change: person %d (%s) old: "%s" new: "%s"',
                $person->person_id,
                $person->getFullName(),
                $person->getInfo()->email,
                $newEmail
            ),
            'email-change'
        );
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
