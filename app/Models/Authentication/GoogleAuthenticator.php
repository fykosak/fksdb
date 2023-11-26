<?php

declare(strict_types=1);

namespace FKSDB\Models\Authentication;

use FKSDB\Models\Authentication\Exceptions\InactiveLoginException;
use FKSDB\Models\Authentication\Exceptions\UnknownLoginException;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\OrganizerModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\LoginService;
use FKSDB\Models\ORM\Services\OrganizerService;
use FKSDB\Models\ORM\Services\PersonService;
use Nette\Security\AuthenticationException;

class GoogleAuthenticator extends AbstractAuthenticator
{

    private OrganizerService $organizerService;
    private AccountManager $accountManager;
    private PersonService $personService;

    public function __construct(
        OrganizerService $organizerService,
        AccountManager $accountManager,
        LoginService $loginService,
        PersonService $personService
    ) {
        parent::__construct($loginService);
        $this->organizerService = $organizerService;
        $this->accountManager = $accountManager;
        $this->personService = $personService;
    }

    /**
     * @throws UnknownLoginException
     * @throws AuthenticationException
     * @throws InactiveLoginException
     * @throws \Exception
     * @phpstan-param array{email:string|null} $user
     */
    public function authenticate(array $user): LoginModel
    {
        $person = $this->findPerson($user);

        if (!$person) {
            throw new UnknownLoginException();
        } else {
            $login = $person->getLogin();
            if (!$login) {
                $login = $this->loginService->createLogin($person);
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
     * @phpstan-param array{email:string|null} $user
     */
    private function findPerson(array $user): ?PersonModel
    {
        if (!$user['email']) {
            throw new AuthenticationException(_('Email not found in the google account.'));
        }
        return $this->findOrganizer($user) ?? $this->personService->findByEmail($user['email']);
    }

    /**
     * @phpstan-param array{email:string|null} $user
     */
    private function findOrganizer(array $user): ?PersonModel
    {
        [$domainAlias, $domain] = explode('@', $user['email']);
        switch ($domain) {
            case 'fykos.cz':
                $contestId = ContestModel::ID_FYKOS;
                break;
            case 'vyfuk.org':
                $contestId = ContestModel::ID_VYFUK;
                break;
            default:
                return null;
        }
        /** @var OrganizerModel|null $organizers */
        $organizers = $this->organizerService->getTable()
            ->where(['domain_alias' => $domainAlias, 'contest_id' => $contestId])
            ->fetch();
        return $organizers ? $organizers->person : null;
    }
}
