<?php

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\YearCalculator;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\GroupedSelection;
use Nette\Utils\Strings;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read int $contest_id
 * @property-read string $name
 */
class ModelContest extends AbstractModelSingle {

    public const ID_FYKOS = 1;
    public const ID_VYFUK = 2;

    public function getContestSymbol(): string {
        return strtolower(Strings::webalize($this->name));
    }

    public function getContestYear(int $year): ?ModelContestYear {
        $row = $this->related(DbNames::TAB_CONTEST_YEAR)->where('year', $year)->fetch();
        return $row ? ModelContestYear::createFromActiveRow($row) : null;
    }

    public function getFirstYear(): int {
        return $this->getContestYears()->min('year');
    }

    public function getLastYear(): int {
        return $this->getContestYears()->max('year');
    }

    public function getContestYears(): GroupedSelection {
        return $this->related(DbNames::TAB_CONTEST_YEAR);
    }

    public function getCurrentYear(): int {
        /** @var ActiveRow|ModelContestYear $row */
        $row = $this->getContestYears()->where('ac_year', YearCalculator::getCurrentAcademicYear())->fetch();
        return $row->year;
    }
}
