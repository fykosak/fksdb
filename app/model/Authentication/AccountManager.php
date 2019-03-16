<?php

namespace Authentication;

use FKSDB\ORM\Models\ModelAuthToken;
use FKSDB\ORM\Models\ModelLogin;
use FKSDB\ORM\Models\ModelPerson;
use Mail\SendFailedException;
use Nette\DateTime;
use Nette\InvalidStateException;
use Nette\Mail\IMailer;
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

    /**
     * @var IMailer
     */
    private $mailer;
    private $invitationExpiration = '+1 month';
    private $recoveryExpiration = '+1 day';
    private $emailFrom;

    /**
     * AccountManager constructor.
     * @param ServiceLogin $serviceLogin
     * @param ServiceAuthToken $serviceAuthToken
     * @param IMailer $mailer
     */
    function __construct(ServiceLogin $serviceLogin, ServiceAuthToken $serviceAuthToken, IMailer $mailer) {
        $this->serviceLogin = $serviceLogin;
        $this->serviceAuthToken = $serviceAuthToken;
        $this->mailer = $mailer;
    }

    /**
     * @return string
     */
    public function getInvitationExpiration() {
        return $this->invitationExpiration;
    }

    /**
     * @param $invitationExpiration
     */
    public function setInvitationExpiration($invitationExpiration) {
        $this->invitationExpiration = $invitationExpiration;
    }

    /**
     * @return string
     */
    public function getRecoveryExpiration() {
        return $this->recoveryExpiration;
    }

    /**
     * @param $recoveryExpiration
     */
    public function setRecoveryExpiration($recoveryExpiration) {
        $this->recoveryExpiration = $recoveryExpiration;
    }

    public function getEmailFrom() {
        return $this->emailFrom;
    }

    /**
     * @param $emailFrom
     */
    public function setEmailFrom($emailFrom) {
        $this->emailFrom = $emailFrom;
    }

    /**
     * Creates login and invites user to set up the account.
     *
     * @param ITemplate $template template of the mail
     * @param \FKSDB\ORM\Models\ModelPerson $person
     * @param string $email
     * @return \FKSDB\ORM\Models\ModelLogin
     * @throws SendFailedException
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
        $message->addTo($email, $person->getFullName());

        try {
            $this->mailer->send($message);
            return $login;
        } catch (InvalidStateException $e) {
            throw new SendFailedException($e);
        }
    }

    /**
     * @param ITemplate $template
     * @param \FKSDB\ORM\Models\ModelLogin $login
     */
    public function sendRecovery(ITemplate $template, ModelLogin $login) {
        $person = $login->getPerson();
        $recoveryAddress = $person ? $person->getInfo()->email : null;
        if (!$recoveryAddress) {
            throw new RecoveryNotImplementedException();
        }
        $token = $this->serviceAuthToken->getTable()->where(array(
            'login_id' => $login->login_id,
            'type' => ModelAuthToken::TYPE_RECOVERY,
        ))
            ->where('until > ?', new DateTime())->fetch();

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

        try {
            $this->mailer->send($message);
        } catch (InvalidStateException $e) {
            throw new SendFailedException($e);
        }
    }

    /**
     * @param \FKSDB\ORM\Models\ModelLogin $login
     */
    public function cancelRecovery(ModelLogin $login) {
        $this->serviceAuthToken->getTable()->where(array(
            'login_id' => $login->login_id,
            'type' => ModelAuthToken::TYPE_RECOVERY,
        ))->delete();
    }

    /**
     * @param ModelPerson $person
     * @param null $login
     * @param null $password
     * @return \AbstractModelSingle|\FKSDB\ORM\Models\ModelLogin
     */
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

/**
 * Class RecoveryException
 * @package Authentication
 */
abstract class RecoveryException extends RuntimeException {

}

/**
 * Class RecoveryExistsException
 * @package Authentication
 */
class RecoveryExistsException extends RecoveryException {

    /**
     * RecoveryExistsException constructor.
     * @param null $previous
     */
    public function __construct($previous = null) {
        $message = _('Obnova účtu již probíhá.');
        $code = null;
        parent::__construct($message, $code, $previous);
    }

}

/**
 * Class RecoveryNotImplementedException
 * @package Authentication
 */
class RecoveryNotImplementedException extends RecoveryException {

    /**
     * RecoveryNotImplementedException constructor.
     * @param null $previous
     */
    public function __construct($previous = null) {
        $message = _('Přístup k účtu nelze obnovit.');
        $code = null;
        parent::__construct($message, $code, $previous);
    }

}

