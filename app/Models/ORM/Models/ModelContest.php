<?php

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\YearCalculator;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\GroupedSelection;
use Nette\Utils\Strings;

/**
 * @property-read int $contest_id
 * @property-read string $name
 */
class ModelContest extends AbstractModel {

    public const ID_FYKOS = 1;
    public const ID_VYFUK = 2;

    public function getContestSymbol(): string {
        return strtolower(Strings::webalize($this->name));
    }

    public function getContestYear(?int $year): ?ModelContestYear {
        $row = $this->getContestYears()->where('year', $year)->fetch();
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

    public function getCurrentContestYear(): ModelContestYear {
        /** @var ActiveRow|ModelContestYear $row */
        $row = $this->getContestYears()->where('ac_year', YearCalculator::getCurrentAcademicYear())->fetch();
        return ModelContestYear::createFromActiveRow($row);
    }
}
