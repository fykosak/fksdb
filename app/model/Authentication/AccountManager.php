<?php

namespace Authentication;

use Mail\SendFailedException;
use ModelAuthToken;
use ModelLogin;
use ModelPerson;
use Nette\DateTime;
use Nette\Diagnostics\Debugger;
use Nette\InvalidStateException;
use Nette\Mail\Message;
use Nette\Templating\ITemplate;
use RuntimeException;
use ServiceAuthToken;
use ServiceLogin;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class AccountManager {

    /**
     * @var ServiceLogin
     */
    private $serviceLogin;

    /**
     * @var ServiceAuthToken
     */
    private $serviceAuthToken;
    private $invitationExpiration = '+1 month';
    private $recoveryExpiration = '+1 day';
    private $emailFrom;

    function __construct(ServiceLogin $serviceLogin, ServiceAuthToken $serviceAuthToken) {
        $this->serviceLogin = $serviceLogin;
        $this->serviceAuthToken = $serviceAuthToken;
    }

    public function getInvitationExpiration() {
        return $this->invitationExpiration;
    }

    public function setInvitationExpiration($invitationExpiration) {
        $this->invitationExpiration = $invitationExpiration;
    }

    public function getRecoveryExpiration() {
        return $this->recoveryExpiration;
    }

    public function setRecoveryExpiration($recoveryExpiration) {
        $this->recoveryExpiration = $recoveryExpiration;
    }

    public function getEmailFrom() {
        return $this->emailFrom;
    }

    public function setEmailFrom($emailFrom) {
        $this->emailFrom = $emailFrom;
    }

    /**
     * Creates login and invites user to set up the account.
     * 
     * @param ITemplate $template template of the mail
     * @param ModelPerson $person
     * @param string $email
     * @return ModelLogin
     * @throws MailNotSendException
     */
    public function createLoginWithInvitation(ITemplate $template, ModelPerson $person, $email) {
        $login = $this->createLogin($person);
        //TODO email

        $this->serviceLogin->save($login);

        $until = DateTime::from($this->getInvitationExpiration());
        $token = $this->serviceAuthToken->createToken($login, ModelAuthToken::TYPE_INITIAL_LOGIN, $until);

        // prepare and send email        
        $template->token = $token->token;
        $template->person = $person;
        $template->email = $email;
        $template->until = $until;

        $message = new Message();
        $message->setHtmlBody($template);
        $message->setSubject(_('Založení účtu'));
        $message->setFrom($this->getEmailFrom());
        $message->addTo($email, $person->getFullname());

        Debugger::log((string) $message->getHtmlBody());

        try {
            $message->send();
            return $login;
        } catch (InvalidStateException $e) {
            throw new SendFailedException(null, null, $e);
        }
    }

    public function sendRecovery(ITemplate $template, ModelLogin $login) {
        $person = $login->getPerson();
        $recoveryAddress = $person ? $person->getInfo()->email : null;
        if (!$recoveryAddress) {
            throw new RecoveryNotImplementedException();
        }
        $token = $this->serviceAuthToken->getTable()->where(array(
                    'login_id' => $login->login_id,
                    'type' => ModelAuthToken::TYPE_RECOVERY,
                ))->fetch();

        if ($token) {
            throw new RecoveryExistsException();
        }

        $until = DateTime::from($this->getRecoveryExpiration());
        $token = $this->serviceAuthToken->createToken($login, ModelAuthToken::TYPE_RECOVERY, $until);

        // prepare and send email        
        $template->token = $token->token;
        $template->login = $login;
        $template->until = $until;

        $message = new Message();
        $message->setHtmlBody($template);
        $message->setSubject(_('Obnova hesla'));
        $message->setFrom($this->getEmailFrom());
        $message->addTo($recoveryAddress, $login->__toString());

        Debugger::log((string) $message->getHtmlBody());

        try {
            $message->send();
        } catch (InvalidStateException $e) {
            throw new MailNotSendException(null, null, $e);
        }
    }

    public function cancelRecovery(ModelLogin $login) {
        $this->serviceAuthToken->getTable()->where(array(
            'login_id' => $login->login_id,
            'type' => ModelAuthToken::TYPE_RECOVERY,
        ))->delete();
    }

    public final function createLogin(ModelPerson $person, $login = null, $password = null) {
        $login = $this->serviceLogin->createNew(array(
            'person_id' => $person->person_id,
            'login' => $login,
            'active' => 1,
        ));

        $this->serviceLogin->save($login);

        /* Must be done after login_id is allocated. */
        if ($password) {
            $login->setHash($password);
            $this->serviceLogin->save($login);
        }
        return $login;
    }

}

abstract class RecoveryException extends RuntimeException {
    
}

class RecoveryExistsException extends RecoveryException {

    public function __construct($previous = null) {
        $message = _('Obnova účtu již probíhá.');
        $code = null;
        parent::__construct($message, $code, $previous);
    }

}

class RecoveryNotImplementedException extends RecoveryException {

    public function __construct($previous = null) {
        $message = _('Přístup k účtu nelze obnovit.');
        $code = null;
        parent::__construct($message, $code, $previous);
    }

}

