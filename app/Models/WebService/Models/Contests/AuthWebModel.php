<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Contests;

use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\OrganizerModel;
use FKSDB\Modules\CoreModule\RestApiPresenter;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

/**
 * @phpstan-extends ContestWebModel<array{contestId:int,app:string},TDatum[]>
 * @phpstan-type TDatum array{
 * name:string,
 * loginId:int|null,
 * login:string|null,
 * hash:string|null,
 * email:string|null,
 * roles:string[],
 * }
 */
class AuthWebModel extends ContestWebModel
{
    public function getExpectedParams(): Structure
    {
        return Expect::structure([
            'contestId' => Expect::int()->required(),
            'app' => Expect::anyOf('wiki', 'pm')->required(),
        ]);
    }

    /**
     * @throws NotFoundException
     */
    protected function getJsonResponse(): array
    {
        $data = [];
        /** @var OrganizerModel $organizer */
        foreach ($this->getContest()->getOrganizers() as $organizer) {
            if (
                $organizer->isActive($this->getContest()->getCurrentContestYear()) ||
                $this->params['app'] === 'wiki' && $organizer->allow_wiki ||
                $this->params['app'] === 'pm' && $organizer->allow_pm
            ) {
                $data[] = $this->getOrgData($organizer);
            }
        }
        return $data;
    }

    /**
     * @phpstan-return TDatum
     * @throws NotFoundException
     */
    private function getOrgData(OrganizerModel $organizer): array
    {
        $personInfo = $organizer->person->getInfo();
        $login = $organizer->person->getLogin();
        $roles = [];
        if ($login) {
            foreach ($login->getContestRoles($this->getContest()) as $grant) {
                $roles[] = $grant->getRoleId();
            }
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
     * @throws NotFoundException
     */
    protected function isAuthorized(): bool
    {
        return $this->contestAuthorizator->isAllowed(RestApiPresenter::RESOURCE_ID, self::class, $this->getContest());
    }
}
