<?php

declare(strict_types=1);

namespace FKSDB\Models\Authentication;

use FKSDB\Models\Authentication\Exceptions\InactiveLoginException;
use FKSDB\Models\Authentication\Exceptions\UnknownLoginException;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\ORM\Models\ModelOrg;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Services\ServiceLogin;
use FKSDB\Models\ORM\Services\ServiceOrg;
use FKSDB\Models\ORM\Services\ServicePerson;
use Nette\Security\AuthenticationException;

class GoogleAuthenticator extends AbstractAuthenticator
{

    private ServiceOrg $serviceOrg;
    private AccountManager $accountManager;
    private ServicePerson $servicePerson;

    public function __construct(
        ServiceOrg $serviceOrg,
        AccountManager $accountManager,
        ServiceLogin $serviceLogin,
        ServicePerson $servicePerson
    ) {
        parent::__construct($serviceLogin);
        $this->serviceOrg = $serviceOrg;
        $this->accountManager = $accountManager;
        $this->servicePerson = $servicePerson;
    }

    /**
     * @throws UnknownLoginException
     * @throws AuthenticationException
     * @throws InactiveLoginException
     * @throws \Exception
     */
    public function authenticate(array $user): ModelLogin
    {
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
     * @throws AuthenticationException
     */
    private function findPerson(array $user): ?ModelPerson
    {
        if (!$user['email']) {
            throw new AuthenticationException(_('Email not found in the google account.'));
        }
        return $this->findOrg($user) ?? $this->servicePerson->findByEmail($user['email']);
    }

    private function findOrg(array $user): ?ModelPerson
    {
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
        $org = $this->serviceOrg->getTable()
            ->where(['domain_alias' => $domainAlias, 'contest_id' => $contestId])
            ->fetch();
        return $org ? $org->getPerson() : null;
    }
}
