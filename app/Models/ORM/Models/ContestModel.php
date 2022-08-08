<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\YearCalculator;
use Fykosak\NetteORM\TypedGroupedSelection;
use Nette\Utils\Strings;

/**
 * @property-read int contest_id
 * @property-read string name
 */
class ContestModel extends Model
{
    public const ID_FYKOS = 1;
    public const ID_VYFUK = 2;

    public function getContestSymbol(): string
    {
        return strtolower(Strings::webalize($this->name));
    }

    public function getContestYear(?int $year): ?ContestYearModel
    {
        return $this->getContestYears()->where('year', $year)->fetch();
    }

    public function getContestYearByAcYear(?int $acYear): ?ContestYearModel
    {
        return $this->getContestYears()->where('ac_year', $acYear)->fetch();
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
        return $this->related(DbNames::TAB_CONTEST_YEAR);
    }

    public function getCurrentContestYear(): ContestYearModel
    {
        return $this->getContestYears()->where('ac_year', YearCalculator::getCurrentAcademicYear())->fetch();
    }
}
