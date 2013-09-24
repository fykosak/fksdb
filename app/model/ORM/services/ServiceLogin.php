<?php

use Nette\Database\Connection;
use Nette\DateTime;
use Nette\Diagnostics\Debugger;
use Nette\InvalidStateException;
use Nette\Mail\Message;
use Nette\Templating\ITemplate;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceLogin extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_LOGIN;
    protected $modelClassName = 'ModelLogin';

    /**
     * @var ServiceAuthToken
     */
    private $authTokenService;

    /**
     * @var string  expecting string like '+10 days'
     */
    private $expiration;

    function __construct($expiration, ServiceAuthToken $authTokenService, Connection $conntection) {
        parent::__construct($conntection);
        $this->authTokenService = $authTokenService;
        $this->expiration = $expiration;
    }

    /**
     * Creates login and invites user to set up the account.
     * 
     * @param string $email
     */
    public function createLoginWithInvitation(ITemplate $template, ModelPerson $person, $email) {
        $login = $this->createNew(array(
            'person_id' => $person->person_id,
            'email' => $email,
            'active' => 1,
        ));

        $this->save($login);

        $until = DateTime::from($this->expiration);
        $token = $this->authTokenService->createToken($login, ModelAuthToken::TYPE_INITIAL_LOGIN, $until);

        // prepare and send email        
        $template->token = $token->token;
        $template->person = $person;
        $template->email = $email;

        $message = new Message();
        $message->setBody($template);
        $message->addTo($email, $person->getFullname());

        Debugger::log($message->getBody()->__toString(true));

        try {
            $message->send();
        } catch (InvalidStateException $e) {
            throw new MailNotSendException(null, null, $e);
        }
    }

}

class MailNotSendException extends RuntimeException {
    
}

