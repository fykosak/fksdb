<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\DbNames;
use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Nette\Security\Resource;

/**
 * @property-read int $banned_person_id
 * @property-read int $person_id
 * @property-read PersonModel $person
 * @property-read string|null $case_id
 * @property-read string|null $note
 * @property-read string|null $scope TODO
 */
class BannedPersonModel extends Model implements Resource
{
    public const RESOURCE_ID = 'bannedPerson';

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }

    /**
     * @phpstan-return TypedGroupedSelection<BannedPersonScopeModel>
     */
    public function getScopes(): TypedGroupedSelection
    {
        /** @var TypedGroupedSelection<BannedPersonScopeModel> $query */
        $query = $this->related(DbNames::TAB_BANNED_PERSON_SCOPE, 'banned_person_id');
        return $query;
    }

    public function getBanForEvent(EventModel $event): ?BannedPersonScopeModel
    {
        /** @var BannedPersonScopeModel|null $model */
        $model = $this->getScopes()
            ->where('event_type_id', $event->event_type_id)
            ->where('begin < ?', $event->begin)
            ->where('end > ? OR end IS NULL', $event->begin)
            ->fetch();
        return $model;
    }

    public function getBanForContestYear(ContestYearModel $contestYear): ?BannedPersonScopeModel
    {
        /** @var BannedPersonScopeModel|null $model */
        $model = $this->getScopes() // TODO tests
            ->where('contest_id', $contestYear->contest_id)
            ->where('begin < ? AND (end > ? OR end IS NULL)', $contestYear->end(), $contestYear->begin())
            ->fetch();
        return $model;
    }
}
