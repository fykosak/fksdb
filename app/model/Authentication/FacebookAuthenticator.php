<?php

namespace Authentication;

use ModelLogin;
use Nette\Security\AuthenticationException;
use Nette\Security\Identity;
use ServiceLogin;
use ServicePerson;
use YearCalculator;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class FacebookAuthenticator extends AbstractAuthenticator {

    /**
     * @var ServicePerson
     */
    private $servicePerson;

    function __construct(ServicePerson $servicePerson, ServiceLogin $serviceLogin, YearCalculator $yearCalculator) {
        parent::__construct($serviceLogin, $yearCalculator);
        $this->servicePerson = $servicePerson;
    }

    /**
     * @param array $fbUser
     * @return Identity
     */
    public function authenticate(array $fbUser) {
        if (!$fbUser['email']) {
            throw new AuthenticationException(_('V profilu Facebooku nebyl nalezen e-mail.'));
        }

        // try by e-mail
        $login = $this->serviceLogin->getTable()
                ->where(array('email' => $fbUser['email']))
                ->fetch();

        // try by FB ID
        if (!$login) {
            $login = $this->serviceLogin->getTable()
                    ->where(array('fb_id' => $fbUser['id']))
                    ->fetch();
        }

        if (!$login) {
            $login = $this->registerFromFB($fbUser);
        } else {
            $login = $this->updateFromFB($login, $fbUser);
        }

        if ($login->active == 0) {
            throw new InactiveLoginException();
        }

        $this->logAuthentication($login);

        return $login;
    }

    public function registerFromFB($fbUser) {
        $person = $this->servicePerson->createNew($this->getPersonData($fbUser));

        $login = $this->serviceLogin->createNew(array(
            'email' => $fbUser['email'],
            'fb_id' => $fbUser['id'],
            'active' => 1,
        ));

        $this->servicePerson->getConnection()->beginTransaction();

        $this->servicePerson->save($person);
        $login->person_id = $person->person_id;
        $this->serviceLogin->save($login);

        $this->servicePerson->getConnection()->commit();

        return $login;
    }

    public function updateFromFB(ModelLogin $login, $fbUser) {
        $loginData = array(
            'email' => $fbUser['email'],
            'fb_id' => $fbUser['id'],
        );
        $this->serviceLogin->updateModel($login, $loginData);

        $person = $login->getPerson();
        
        $personData = $this->getPersonData($fbUser);
        // there can be bullshit in this fields, so don't use it for update
        unset($personData['family_name']);
        unset($personData['other_name']);
        unset($personData['display_name']);        
        $this->servicePerson->updateModel($person, $personData);

        $this->servicePerson->getConnection()->beginTransaction();
        $this->serviceLogin->save($login);
        $this->servicePerson->save($person);
        $this->servicePerson->getConnection()->commit();

        return $login;
    }

    private function getPersonData($fbUser) {
        return array(
            'family_name' => $fbUser['last_name'],
            'other_name' => $fbUser['first_name'],
            'display_name' => ($fbUser['first_name'] . ' ' . $fbUser['last_name'] != $fbUser['name']) ? $fbUser['name'] : null,
            'gender' => ($fbUser['gender']) == 'female' ? 'F' : 'M',
        );
    }

}
