<?php

namespace FKSDB\Models\Authentication;

use FKSDB\Models\Authentication\Exceptions\RecoveryExistsException;
use FKSDB\Models\Authentication\Exceptions\RecoveryNotImplementedException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Localization\UnsupportedLanguageException;
use FKSDB\Models\ORM\Models\ModelAuthToken;
use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Services\ServiceAuthToken;
use FKSDB\Models\ORM\Services\ServiceEmailMessage;
use FKSDB\Models\ORM\Services\ServiceLogin;
use FKSDB\Models\Mail\MailTemplateFactory;
use Nette\Utils\DateTime;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class AccountManager {

    private ServiceLogin $serviceLogin;
    private ServiceAuthToken $serviceAuthToken;
    private string $invitationExpiration;
    private string $recoveryExpiration;
    private string $emailFrom;
    private ServiceEmailMessage $serviceEmailMessage;
    private MailTemplateFactory $mailTemplateFactory;

    public function __construct(
        string $invitationExpiration,
        string $recoveryExpiration,
        string $emailFrom,
        MailTemplateFactory $mailTemplateFactory,
        ServiceLogin $serviceLogin,
        ServiceAuthToken $serviceAuthToken,
        ServiceEmailMessage $serviceEmailMessage
    ) {
        $this->invitationExpiration = $invitationExpiration;
        $this->recoveryExpiration = $recoveryExpiration;
        $this->emailFrom = $emailFrom;
        $this->serviceLogin = $serviceLogin;
        $this->serviceAuthToken = $serviceAuthToken;
        $this->serviceEmailMessage = $serviceEmailMessage;
        $this->mailTemplateFactory = $mailTemplateFactory;
    }

    /**
     * Creates login and invites user to set up the account.
     *
     * @param ModelPerson $person
     * @param string $email
     * @param string $lang
     * @return ModelLogin
     * @throws UnsupportedLanguageException
     * @throws BadTypeException
     * @throws \Exception
     */
    public function createLoginWithInvitation(ModelPerson $person, string $email, string $lang): ModelLogin {
        $login = $this->createLogin($person);

        $until = DateTime::from($this->invitationExpiration);
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
        $data['sender'] = $this->emailFrom;
        $data['recipient'] = $email;
        $this->serviceEmailMessage->addMessageToSend($data);
        return $login;
    }

    /**
     * @param ModelLogin $login
     * @param string|null $lang
     * @return void
     * @throws UnsupportedLanguageException
     * @throws BadTypeException
     * @throws \Exception
     */
    public function sendRecovery(ModelLogin $login, string $lang): void {
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

        $until = DateTime::from($this->recoveryExpiration);
        $token = $this->serviceAuthToken->createToken($login, ModelAuthToken::TYPE_RECOVERY, $until);
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
