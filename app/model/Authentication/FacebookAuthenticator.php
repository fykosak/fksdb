<?php

namespace Authentication;

use FKSDB\Authentication\AccountManager;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServiceLogin;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\ORM\Services\ServicePersonInfo;
use FKSDB\YearCalculator;
use Nette\Security\AuthenticationException;
use Nette\Security\Identity;
use Tracy\Debugger;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class FacebookAuthenticator extends AbstractAuthenticator {

    /**
     * @var \FKSDB\ORM\Services\ServicePerson
     */
    private $servicePerson;

    /**
     * @var \FKSDB\ORM\Services\ServicePersonInfo
     */
    private $servicePersonInfo;

    /**
     * @var AccountManager
     */
    private $accountManager;

    /**
     * FacebookAuthenticator constructor.
     * @param ServicePerson $servicePerson
     * @param \FKSDB\ORM\Services\ServicePersonInfo $servicePersonInfo
     * @param AccountManager $accountManager
     * @param \FKSDB\ORM\Services\ServiceLogin $serviceLogin
     * @param \FKSDB\YearCalculator $yearCalculator
     */
    function __construct(ServicePerson $servicePerson, ServicePersonInfo $servicePersonInfo, AccountManager $accountManager, ServiceLogin $serviceLogin, YearCalculator $yearCalculator) {
        parent::__construct($serviceLogin, $yearCalculator);
        $this->servicePerson = $servicePerson;
        $this->servicePersonInfo = $servicePersonInfo;
        $this->accountManager = $accountManager;
    }

    /**
     * @param array $fbUser
     * @return Identity
     * @throws AuthenticationException
     * @throws InactiveLoginException
     */
    public function authenticate(array $fbUser) {
        $person = $this->findPerson($fbUser);

        if (!$person) {
            $login = $this->registerFromFB($fbUser);
        } else {
            $login = $person->getLogin();
            if (!$login) {
                $login = $this->accountManager->createLogin($person);
            }
            $this->updateFromFB($person, $fbUser);
        }

        if ($login->active == 0) {
            throw new InactiveLoginException();
        }

        $this->logAuthentication($login);

        return $login;
    }

    /**
     * @param array $fbUser
     * @return \Nette\Database\Table\ActiveRow|null
     * @throws AuthenticationException
     */
    private function findPerson(array $fbUser) {
        if (!$fbUser['email']) {
            throw new AuthenticationException(_('V profilu Facebooku nebyl nalezen e-mail.'));
        }

        // try both e-mail and FB ID
        $result = $this->servicePerson->getTable()->where('person_info:email = ? OR person_info:fb_id = ?', $fbUser['email'], $fbUser['id']);
        if (count($result) > 1) {
            throw new AuthenticationException(_('Facebook účtu odpovídá více osob.'));
        } elseif (count($result) == 0) {
            return null;
        } else {
            return $result->fetch();
        }
    }

    /**
     * @param $fbUser
     * @return \FKSDB\ORM\AbstractModelSingle|\FKSDB\ORM\Models\ModelLogin
     * @throws \Exception
     */
    private function registerFromFB($fbUser) {
        $person = $this->servicePerson->createNew($this->getPersonData($fbUser));
        $personInfo = $this->servicePersonInfo->createNew($this->getPersonInfoData($fbUser));

        $this->servicePerson->getConnection()->beginTransaction();

        $this->servicePerson->save($person);

        $personInfo->person_id = $person->person_id;
        $this->servicePersonInfo->save($personInfo);

        $login = $this->accountManager->createLogin($person);

        $this->servicePerson->getConnection()->commit();

        return $login;
    }

    /**
     * @param ModelPerson $person
     * @param $fbUser
     * @throws \Exception
     */
    private function updateFromFB(ModelPerson $person, $fbUser) {
        $personData = $this->getPersonData($fbUser);
        // there can be bullshit in this fields, so don't use it for update
        unset($personData['family_name']);
        unset($personData['other_name']);
        unset($personData['display_name']);
        $this->servicePerson->updateModel($person, $personData);

        $personInfo = $person->getInfo();
        $personInfoData = $this->getPersonInfoData($fbUser);
        /* If we have e-mail that is different from FB's one, don't modify it,
         * however, mark it to the log.
         */
        if (isset($personInfo->email)) {
            if (isset($personInfoData['email']) && $personInfoData['email'] !== $personInfo->email) {
                Debugger::log(sprintf('Our email: %s, FB email %s', $personInfo->email, $personInfoData['email']));
            }
            unset($personInfoData['email']);
        }
        /* Email nor fb_id can violate unique constraint here as we've used it to identify the person in authenticate. */
        $this->servicePersonInfo->updateModel($personInfo, $personInfoData);

        $this->servicePerson->getConnection()->beginTransaction();
        $this->servicePerson->save($person);
        $this->servicePersonInfo->save($personInfo);
        $this->servicePerson->getConnection()->commit();
    }

    /**
     * @param $fbUser
     * @return array
     */
    private function getPersonData($fbUser) {
        return [
            'family_name' => $fbUser['last_name'],
            'other_name' => $fbUser['first_name'],
            'display_name' => ($fbUser['first_name'] . ' ' . $fbUser['last_name'] != $fbUser['name']) ? $fbUser['name'] : null,
            'gender' => ($fbUser['gender']) == 'female' ? 'F' : 'M',
        ];
    }

    /**
     * @param $fbUser
     * @return array
     */
    private function getPersonInfoData($fbUser) {
        return [
            'email' => $fbUser['email'],
            'fb_id' => $fbUser['id'],
        ];
    }

}
