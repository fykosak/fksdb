<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Contests;

use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\OrganizerModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\ContestService;
use FKSDB\Models\WebService\Models\WebModel;
use FKSDB\Modules\CoreModule\RestApiPresenter;
use Nette\Security\Permission;
use Tracy\Debugger;

/**
 * @phpstan-extends ContestWebModel<TDatum[]>
 * @phpstan-type TDatum array{
 *   name:string,
 *   otherName:string,
 *   familyName:string,
 *   loginId:int|null,
 *   personId: int,
 *   login:string|null,
 *   hash:string|null,
 *   emails:array<string,string|null>,
 *   roles:string[],
 *   }
 */
class AuthWebModel extends WebModel
{
    private Permission $permission;
    private ContestService $contestService;

    public const Systems = [
        'wiki',
        'pm',
        'astrid',
    ];

    public function injectPermission(Permission $permission, ContestService $contestService): void
    {
        $this->permission = $permission;
        $this->contestService = $contestService;
    }

    public function getExpectedParams(): array
    {
        return [];
    }

    protected function getJsonResponse(): array
    {
        $persons = [];
        /** @var ContestModel $contest */
        foreach ($this->contestService->getTable() as $contest) {
            /** @var OrganizerModel $organizer */
            foreach ($contest->getOrganizers() as $organizer) {
                $active = $organizer->isActive($contest->getCurrentContestYear());
                $persons[$organizer->person_id] ??= $this->serializePerson($organizer->person);
                $persons[$organizer->person_id]['emails'][$organizer->contest->getContestSymbol()]
                    = $organizer->formatDomainEmail();
                foreach (self::Systems as $system) {
                    if ($active || $this->isByPassed($system, $organizer)) {
                        $persons[$organizer->person_id]['roles'] = [
                            ... $persons[$organizer->person_id]['roles'],
                            ... $this->getRoles($organizer, $system),
                        ];
                    }
                }
            }
        }
        return array_values($persons);
    }

    /**
     * @phpstan-return array{
     *  name:string,
     *  otherName:string,
     *  familyName:string,
     *  loginId:int|null,
     *  personId: int,
     *  login:string|null,
     *  hash:string|null,
     *  emails:array<string,string|null>,
     *  roles:string[],
     *  }
     */
    private function serializePerson(PersonModel $person): array
    {
        $personInfo = $person->getInfo();
        $login = $person->getLogin();
        return [
            'otherName' => $person->other_name,
            'familyName' => $person->family_name,
            'name' => $person->getFullName(),
            'personId' => $person->person_id,
            'loginId' => $login ? $login->login_id : null,
            'login' => $login ? $login->login : null,
            'hash' => $login ? $login->hash : null,
            'emails' => $personInfo ? ['personal' => $personInfo->email] : [],
            'roles' => []
        ];
    }

    private function isByPassed(string $system, OrganizerModel $organizer): bool
    {
        switch ($system) {
            case 'wiki':
                return (bool)$organizer->allow_wiki;
            case 'pm':
                return (bool)$organizer->allow_pm;
            default:
                return false;
        }
    }

    /**
     * @return string[]
     */
    private function getRoles(OrganizerModel $organizer, string $system): array
    {
        $login = $organizer->person->getLogin();
        if (!$login) {
            return [];
        }
        $roles = [];
        foreach ($login->getContestRoles($organizer->contest) as $grant) {
            $roles[$grant->getRoleId()] = $grant->getRoleId();
        }
        foreach ($login->getExplicitBaseRoles() as $grant) {
            $roles[$grant->getRoleId()] = $grant->getRoleId();
        }
        $this->calculateParents($roles);
        $newRoles = [];
        foreach ($roles as $role) {
            $newRoles[] = 'fksdb/' . $organizer->contest->getContestSymbol() . '/' . $system . '/' . explode('.', $role)[1];
        }
        return $newRoles;
    }

    /**
     * @param string[] $roles
     */
    private function calculateParents(array &$roles): void
    {
        foreach ($roles as $role) {
            $parents = $this->permission->getRoleParents($role);

            $this->calculateParents($parents);
            foreach ($parents as $parent) {
                $roles[$parent] = $parent;
            }
        }
    }

    protected function isAuthorized(): bool
    {
        return $this->contestAuthorizator->isAllowedAnyContest(
            RestApiPresenter::RESOURCE_ID,
            self::class
        );
    }
}
