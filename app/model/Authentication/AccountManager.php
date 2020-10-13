<?php

namespace FKSDB\Authentication;

use FKSDB\Localization\UnsupportedLanguageException;
use FKSDB\ORM\Models\ModelAuthToken;
use FKSDB\ORM\Models\ModelLogin;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServiceAuthToken;
use FKSDB\ORM\Services\ServiceEmailMessage;
use FKSDB\ORM\Services\ServiceLogin;
use FKSDB\Mail\MailTemplateFactory;
use Nette\Utils\DateTime;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class AccountManager {

    private ServiceLogin $serviceLogin;
    private ServiceAuthToken $serviceAuthToken;
    private string $invitationExpiration = '+1 month';
    private string $recoveryExpiration = '+1 day';
    private string $emailFrom;
    private ServiceEmailMessage $serviceEmailMessage;
    private MailTemplateFactory $mailTemplateFactory;

    public function __construct(
        MailTemplateFactory $mailTemplateFactory,
        ServiceLogin $serviceLogin,
        ServiceAuthToken $serviceAuthToken,
        ServiceEmailMessage $serviceEmailMessage
    ) {
        $this->serviceLogin = $serviceLogin;
        $this->serviceAuthToken = $serviceAuthToken;
        $this->serviceEmailMessage = $serviceEmailMessage;
        $this->mailTemplateFactory = $mailTemplateFactory;
    }

    public function getInvitationExpiration(): string {
        return $this->invitationExpiration;
    }

    public function setInvitationExpiration(string $invitationExpiration): void {
        $this->invitationExpiration = $invitationExpiration;
    }

    public function getRecoveryExpiration(): string {
        return $this->recoveryExpiration;
    }

    public function setRecoveryExpiration(string $recoveryExpiration): void {
        $this->recoveryExpiration = $recoveryExpiration;
    }

    public function getEmailFrom(): string {
        return $this->emailFrom;
    }

    public function setEmailFrom(string $emailFrom): void {
        $this->emailFrom = $emailFrom;
    }

    /**
     * Creates login and invites user to set up the account.
     *
     * @param ModelPerson $person
     * @param string $email
     * @param string $lang
     * @return ModelLogin
     * @throws UnsupportedLanguageException
     */
    public function createLoginWithInvitation(ModelPerson $person, string $email, string $lang): ModelLogin {
        $login = $this->createLogin($person);

        $until = DateTime::from($this->getInvitationExpiration());
        $token = $this->serviceAuthToken->createToken($login, ModelAuthToken::TYPE_INITIAL_LOGIN, $until);

        $templateParams = [
            'token' => $token->token,
            'person' => $person,
            'email' => $email,
            'until' => $until,
        ];
        $data = [];
        $data['text'] = (string)$this->mailTemplateFactory->createLoginInvitation($person->getPreferredLang() ?: $lang, $templateParams);
        $data['subject'] = _('Create an account');
        $data['sender'] = $this->getEmailFrom();
        $data['recipient'] = $email;
        $this->serviceEmailMessage->addMessageToSend($data);
        return $login;
    }

    /**
     * @param ModelLogin $login
     * @param string|null $lang
     * @return void
     * @throws UnsupportedLanguageException
     */
    public function sendRecovery(ModelLogin $login, ?string $lang = null): void {
        $person = $login->getPerson();
        $recoveryAddress = $person ? $person->getInfo()->email : null;
        if (!$recoveryAddress) {
            throw new RecoveryNotImplementedException();
        }
        $token = $this->serviceAuthToken->getTable()->where([
            'login_id' => $login->login_id,
            'type' => ModelAuthToken::TYPE_RECOVERY,
        ])
            ->where('until > ?', new DateTime())->fetch();
        if ($token) {
            throw new RecoveryExistsException();
        }

        $until = DateTime::from($this->getRecoveryExpiration());
        $token = $this->serviceAuthToken->createToken($login, ModelAuthToken::TYPE_RECOVERY, $until);
        $templateParams = [
            'token' => $token->token,
            'login' => $login,
            'until' => $until,
        ];
        $data = [];
        $data['text'] = (string)$this->mailTemplateFactory->createPasswordRecovery($lang, $templateParams);
        $data['subject'] = _('Password recovery');
        $data['sender'] = $this->getEmailFrom();
        $data['recipient'] = $recoveryAddress;

        $this->serviceEmailMessage->addMessageToSend($data);
    }

    public function cancelRecovery(ModelLogin $login): void {
        $this->serviceAuthToken->getTable()->where([
            'login_id' => $login->login_id,
            'type' => ModelAuthToken::TYPE_RECOVERY,
        ])->delete();
    }

    final public function createLogin(ModelPerson $person, ?string $login = null, ?string $password = null): ModelLogin {
        /** @var ModelLogin $login */
        $login = $this->serviceLogin->createNewModel([
            'person_id' => $person->person_id,
            'login' => $login,
            'active' => 1,
        ]);

        /* Must be done after login_id is allocated. */
        if ($password) {
            $hash = $login->createHash($password);
            $this->serviceLogin->updateModel2($login, ['hash' => $hash]);
        }
        return $login;
    }
}
