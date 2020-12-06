<?php

namespace FKSDB\Authentication;

use FKSDB\Authentication\Exceptions\InactiveLoginException;
use FKSDB\Authentication\Exceptions\UnknownLoginException;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelLogin;
use FKSDB\ORM\Models\ModelOrg;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServiceLogin;
use FKSDB\ORM\Services\ServiceOrg;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\YearCalculator;
use Nette\Security\AuthenticationException;

/**
 * Class GoogleAuthenticator
 * @author Michal Červeňák <miso@fykos.cz>
 */
class GoogleAuthenticator extends AbstractAuthenticator {

    private ServiceOrg $serviceOrg;
    private AccountManager $accountManager;
    private ServicePerson $servicePerson;

    public function __construct(ServiceOrg $serviceOrg, AccountManager $accountManager, ServiceLogin $serviceLogin, YearCalculator $yearCalculator, ServicePerson $servicePerson) {
        parent::__construct($serviceLogin, $yearCalculator);
        $this->serviceOrg = $serviceOrg;
        $this->accountManager = $accountManager;
        $this->servicePerson = $servicePerson;
    }

    /**
     * @param array $user
     * @return ModelLogin
     * @throws UnknownLoginException
     * @throws AuthenticationException
     * @throws InactiveLoginException
     * @throws \Exception
     */
    public function authenticate(array $user): ModelLogin {
        $person = $this->findPerson($user);

        if (!$person) {
            throw new UnknownLoginException();
        } else {
            $login = $person->getLogin();
            if (!$login) {
                $login = $this->accountManager->createLogin($person);
            }
        }
        if ($login->active == 0) {
            throw new InactiveLoginException();
        }
        $this->logAuthentication($login);
        return $login;
    }

    /**
     * @param array $user
     * @return ModelPerson|null
     * @throws AuthenticationException
     */
    private function findPerson(array $user): ?ModelPerson {
        if (!$user['email']) {
            throw new AuthenticationException(_('V profilu Google nebyl nalezen e-mail.'));
        }
        return $this->findOrg($user) ?? $this->servicePerson->findByEmail($user['email']);
    }

    private function findOrg(array $user): ?ModelPerson {
        [$domainAlias, $domain] = explode('@', $user['email']);
        switch ($domain) {
            case 'fykos.cz':
                $contestId = ModelContest::ID_FYKOS;
                break;
            case 'vyfuk.org':
                $contestId = ModelContest::ID_VYFUK;
                break;
            default:
                return null;
        }
        /** @var ModelOrg|null $org */
        $org = $this->serviceOrg->getTable()->where(['domain_alias' => $domainAlias, 'contest_id' => $contestId])->fetch();
        return $org ? $org->getPerson() : null;
    }
}
