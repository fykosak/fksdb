<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\Authorization\Resource\ContestResource;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Services\ContestYearService;
use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;

/**
 * @property-read int $contest_id
 * @property-read string $name
 */
final class ContestModel extends Model implements ContestResource
{
    public const RESOURCE_ID = 'contest';

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
     * @phpstan-return TypedGroupedSelection<OrganizerModel>
     */
    public function getOrganizers(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<OrganizerModel> $selection */
        $selection = $this->related(DbNames::TAB_ORGANIZER, 'contest_id');
        return $selection;
    }

    /**
     * @phpstan-return TypedGroupedSelection<TaskModel>
     */
    public function getTasks(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<TaskModel> $selection */
        $selection = $this->related(DbNames::TAB_TASK, 'contest_id');
        return $selection;
    }

    /**
     * @phpstan-return TypedGroupedSelection<EventTypeModel>
     */
    public function getEventTypes(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<EventTypeModel> $selection */
        $selection = $this->related(DbNames::TAB_EVENT_TYPE, 'contest_id');
        return $selection;
    }

    /**
     * @phpstan-return TypedGroupedSelection<ContestYearModel>
     */
    public function getContestYears(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<ContestYearModel> $selection */
        $selection = $this->related(DbNames::TAB_CONTEST_YEAR, 'contest_id');
        return $selection;
    }

    /**
     * @throws NotFoundException
     */
    public function getContestYear(int $year): ContestYearModel
    {
        /** @var ContestYearModel|null $contestYear */
        $contestYear = $this->getContestYears()->where('year', $year)->fetch();
        if (!$contestYear) {
            throw new NotFoundException();
        }
        return $contestYear;
    }

    /**
     * @phpstan-return ContestYearModel[]
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

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }

    public function getContest(): ContestModel
    {
        return $this;
    }
}
