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
class ContestModel extends Model
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

    public function getContestYear(?int $year): ?ContestYearModel
    {
        /** @var ContestYearModel $contestYear */
        $contestYear = $this->getContestYears()->where('year', $year)->fetch();
        return $contestYear;
    }

    public function getContestYearByAcYear(?int $acYear): ?ContestYearModel
    {
        /** @var ContestYearModel $contestYear */
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

    public function getContestYears(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_CONTEST_YEAR, 'contest_id');
    }

    public function getForwardedYear(): ?ContestYearModel
    {
        /** @var ContestYearModel $contestYear */
        $contestYear = $this->getContestYears()->where(
            'ac_year > ?',
            ContestYearService::getCurrentAcademicYear()
        )->fetch();
        return $contestYear;
    }

    public function getCurrentContestYear(): ContestYearModel
    {
        /** @var ContestYearModel $contestYear */
        $contestYear = $this->getContestYears()->where('ac_year', ContestYearService::getCurrentAcademicYear())->fetch(
        );
        return $contestYear;
    }

    public function getOrganisers(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_ORG, 'contest_id');
    }

    public function getTasks(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_TASK, 'contest_id');
    }

    public function getEventTypes(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_EVENT_TYPE, 'contest_id');
    }
}
