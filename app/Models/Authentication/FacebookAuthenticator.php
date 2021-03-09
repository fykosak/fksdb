<?php

namespace FKSDB\Models\Authentication;

use FKSDB\Models\Authentication\Exceptions\InactiveLoginException;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Services\ServiceLogin;
use FKSDB\Models\ORM\Services\ServicePerson;
use FKSDB\Models\ORM\Services\ServicePersonInfo;
use Nette\Database\Table\ActiveRow;
use Nette\Security\AuthenticationException;
use Tracy\Debugger;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class FacebookAuthenticator extends AbstractAuthenticator {

    private ServicePerson $servicePerson;
    private ServicePersonInfo $servicePersonInfo;
    private AccountManager $accountManager;

    public function __construct(ServicePerson $servicePerson, ServicePersonInfo $servicePersonInfo, AccountManager $accountManager, ServiceLogin $serviceLogin) {
        parent::__construct($serviceLogin);
        $this->servicePerson = $servicePerson;
        $this->servicePersonInfo = $servicePersonInfo;
        $this->accountManager = $accountManager;
    }

    /**
     * @param array $fbUser
     * @return AbstractModel|ModelLogin
     * @throws AuthenticationException
     * @throws InactiveLoginException
     * @throws \Exception
     */
    public function authenticate(array $fbUser): ModelLogin {
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
     * @return ModelPerson|ActiveRow|null
     * @throws AuthenticationException
     */
    private function findPerson(array $fbUser): ?ModelPerson {
        if (!$fbUser['email']) {
            throw new AuthenticationException(_('V profilu Facebooku nebyl nalezen e-mail.'));
        }

        // try both e-mail and FB ID
        $result = $this->servicePerson->getTable()->where(':person_info.email = ? OR person_info.fb_id = ?', $fbUser['email'], $fbUser['id']);
        if (count($result) > 1) {
            throw new AuthenticationException(_('Facebook účtu odpovídá více osob.'));
        } elseif (count($result) == 0) {
            return null;
        } else {
            return $result->fetch();
        }
    }

    private function registerFromFB(array $fbUser): ModelLogin {
        $this->servicePerson->getExplorer()->getConnection()->beginTransaction();
        $person = $this->servicePerson->createNewModel($this->getPersonData($fbUser));
        $this->servicePersonInfo->createNewModel(array_merge(['person_id' => $person->person_id], $this->getPersonInfoData($fbUser)));
        $login = $this->accountManager->createLogin($person);
        $this->servicePerson->getExplorer()->getConnection()->commit();
        return $login;
    }

    private function updateFromFB(ModelPerson $person, array $fbUser): void {
        $this->servicePerson->getExplorer()->getConnection()->beginTransaction();
        $personData = $this->getPersonData($fbUser);
        // there can be bullshit in this fields, so don't use it for update
        unset($personData['family_name']);
        unset($personData['other_name']);
        unset($personData['display_name']);
        $this->servicePerson->updateModel2($person, $personData);

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
        $this->servicePersonInfo->updateModel2($personInfo, $personInfoData);

        $this->servicePerson->getExplorer()->getConnection()->commit();
    }

    private function getPersonData(array $fbUser): array {
        return [
            'family_name' => $fbUser['last_name'],
            'other_name' => $fbUser['first_name'],
            'display_name' => ($fbUser['first_name'] . ' ' . $fbUser['last_name'] != $fbUser['name']) ? $fbUser['name'] : null,
            'gender' => ($fbUser['gender']) == 'female' ? 'F' : 'M',
        ];
    }

    private function getPersonInfoData(array $fbUser): array {
        return [
            'email' => $fbUser['email'],
            'fb_id' => $fbUser['id'],
        ];
    }
}
