<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\Authorization\ContestAuthorizator;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\GrantModel;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\OrganizerModel;
use FKSDB\Models\ORM\Services\ContestService;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

/**
 * @phpstan-extends WebModel<array{contestId:int,app:string},TDatum[]>
 * @phpstan-type TDatum array{
 * name:string,
 * loginId:int|null,
 * login:string|null,
 * hash:string|null,
 * email:string|null,
 * roles:string[],
 * }
 */
class AuthWebModel extends WebModel
{
    private ContestService $service;
    private ContestAuthorizator $authorizator;

    public function inject(ContestService $service, ContestAuthorizator $authorizator): void
    {
        $this->authorizator = $authorizator;
        $this->service = $service;
    }

    public function getExpectedParams(): Structure
    {
        return Expect::structure([
            'contestId' => Expect::scalar()->castTo('int')->required(),
            'app' => Expect::anyOf('wiki', 'pm')->required(),
        ]);
    }

    /**
     * @throws NotFoundException
     */
    protected function getJsonResponse(array $params): array
    {
        /** @var ContestModel|null $contest */
        $contest = $this->service->findByPrimary($params['contestId']);
        if (!$contest) {
            throw new NotFoundException();
        }
        $this->authorizator->isAllowed('webService', 'auth', $contest);
        $data = [];
        /** @var OrganizerModel $organizer */
        foreach ($contest->getOrganizers() as $organizer) {
            if (
                $organizer->isActive($contest->getCurrentContestYear()) ||
                $params['app'] === 'wiki' && $organizer->allow_wiki ||
                $params['app'] === 'pm' && $organizer->allow_pm
            ) {
                $data[] = $this->getOrgData($organizer);
            }
        }
        return $data;
    }

    /**
     * @phpstan-return TDatum
     */
    private function getOrgData(OrganizerModel $organizer): array
    {
        $personInfo = $organizer->person->getInfo();
        $login = $organizer->person->getLogin();
        $roles = $login ? $this->getRoles($login, $organizer->contest) : [];
        if ($organizer->isActive($organizer->contest->getCurrentContestYear())) {
            $roles[] = 'org';
        }
        return [
            'name' => $organizer->person->getFullName(),
            'loginId' => $login ? $login->login_id : null,
            'login' => $login ? $login->login : null,
            'hash' => $login ? $login->hash : null,
            'email' => $personInfo ? $personInfo->email : null,
            'roles' => $roles,
        ];
    }

    /**
     * @return string[]
     */
    private function getRoles(LoginModel $login, ContestModel $contest): array
    {
        $data = [];
        /** @var GrantModel $grant */
        foreach ($login->getGrants()->where('contest_id', $contest->contest_id) as $grant) {
            $data[] = $grant->role->name;
        }
        return $data;
    }
}
