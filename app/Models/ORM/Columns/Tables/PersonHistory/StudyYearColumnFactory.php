<?php

namespace FKSDB\Models\ORM\Columns\Tables\PersonHistory;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\ModelContestYear;
use FKSDB\Models\ValuePrinters\StringPrinter;
use FKSDB\Models\YearCalculator;
use Fykosak\NetteORM\AbstractModel;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Utils\Html;

class StudyYearColumnFactory extends ColumnFactory
{

    /**
     * @param array $args
     * @return BaseControl
     * @throws \InvalidArgumentException
     */
    protected function createFormControl(...$args): BaseControl
    {
        [$contestYear] = $args;
        if (\is_null($contestYear)) {
            throw new \InvalidArgumentException();
        }
        $control = new SelectBox($this->getTitle());
        $control->setItems($this->createOptions($contestYear));
        $control->setOption('description', $this->getDescription());
        $control->setPrompt(_('Choose study year'));
        return $control;
    }

    private function createOptions(ModelContestYear $contestYear): array
    {
        $hsYears = [];
        foreach (range(1, 4) as $studyYear) {
            $hsYears[$studyYear] = sprintf(
                _('grade %d (expected graduation in %d)'),
                $studyYear,
                YearCalculator::getGraduationYear($studyYear, $contestYear)
            );
        }

        $primaryYears = [];
        foreach (range(6, 9) as $studyYear) {
            $primaryYears[$studyYear] = sprintf(
                _('grade %d (expected graduation in %d)'),
                $studyYear,
                YearCalculator::getGraduationYear($studyYear, $contestYear)
            );
        }

        return [
            _('high school') => $hsYears,
            _('primary school') => $primaryYears,
        ];
    }

    protected function createHtmlValue(AbstractModel $model): Html
    {
        return (new StringPrinter())($model->{$this->getModelAccessKey()});
    }
}
