<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Services\ContestYearService;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\TypedGroupedSelection;

/**
 * @property-read int $contest_id
 * @property-read string $name
 */
final class ContestModel extends Model
{
    public const ID_FYKOS = 1;
    public const ID_VYFUK = 2;

    public function getContestSymbol(): string
    {
        switch ($this->contest_id) {
            case 1:
                return 'fykos';
            case 2:
                return 'vyfuk';
            case 3:
                return 'ctyrboj';
        }
        throw new \InvalidArgumentException();
    }

    /**
     * @phpstan-return TypedGroupedSelection<OrgModel>
     */
    public function getOrganisers(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_ORG, 'contest_id');
    }

    /**
     * @phpstan-return TypedGroupedSelection<TaskModel>
     */
    public function getTasks(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_TASK, 'contest_id');
    }

    /**
     * @phpstan-return TypedGroupedSelection<EventTypeModel>
     */
    public function getEventTypes(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_EVENT_TYPE, 'contest_id');
    }

    /**
     * @phpstan-return TypedGroupedSelection<ContestYearModel>
     */
    public function getContestYears(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_CONTEST_YEAR, 'contest_id');
    }

    public function getContestYear(?int $year): ?ContestYearModel
    {
        /** @var ContestYearModel|null $contestYear */
        $contestYear = $this->getContestYears()->where('year', $year)->fetch();
        return $contestYear;
    }

    /**
     * @return ContestYearModel[]
     */
    public function getActiveYears(): array
    {
        $years = [];
        /** @var ContestYearModel $contestYear */
        foreach ($this->getContestYears() as $contestYear) {
            if ($contestYear->isActive()) {
                $years[] = $contestYear;
            }
        }
        return $years;
    }

    public function getContestYearByAcYear(?int $acYear): ?ContestYearModel
    {
        /** @var ContestYearModel|null $contestYear */
        $contestYear = $this->getContestYears()->where('ac_year', $acYear)->fetch();
        return $contestYear;
    }

    public function getFirstYear(): int
    {
        return $this->getContestYears()->min('year');
    }

    public function getLastYear(): int
    {
        return $this->getContestYears()->max('year');
    }

    public function getCurrentContestYear(): ?ContestYearModel
    {
        /** @var ContestYearModel|null $contestYear */
        $contestYear = $this->getContestYears()
            ->where('ac_year', ContestYearService::getCurrentAcademicYear())
            ->fetch();
        return $contestYear;
    }

    /**
     * @phpstan-return array{
     *      contestId:int,
     *      contest:string,
     *      name:string,
     *      currentYear:int,
     *      firstYear:int,
     *      lastYear:int,
     * }
     */
    public function __toArray(): array
    {
        return [
            'contestId' => $this->contest_id,
            'contest' => $this->getContestSymbol(),
            'name' => $this->name,
            'currentYear' => $this->getCurrentContestYear()->year,
            'firstYear' => $this->getFirstYear(),
            'lastYear' => $this->getLastYear(),
        ];
    }
}
