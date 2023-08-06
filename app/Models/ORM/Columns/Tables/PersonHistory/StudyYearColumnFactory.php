<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\PersonHistory;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\Models\StudyYear;
use FKSDB\Models\ValuePrinters\StringPrinter;
use Fykosak\NetteORM\Model;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<PersonHistoryModel,ContestYearModel>
 */
class StudyYearColumnFactory extends ColumnFactory
{

    /**
     * @throws \InvalidArgumentException
     */
    protected function createFormControl(...$args): BaseControl
    {
        [$contestYear] = $args;
        if (!$contestYear instanceof ContestYearModel) {
            throw new \InvalidArgumentException();
        }
        $control = new SelectBox($this->getTitle());
        $control->setItems($this->createOptions($contestYear));
        $control->setOption('description', $this->getDescription());
        $control->setPrompt(_('Choose study year'));
        return $control;
    }

    /**
     * @return array<string,array<int,string>>
     */
    private function createOptions(ContestYearModel $contestYear): array
    {
        $hsYears = [];
        foreach (StudyYear::getHighSchoolCases() as $studyYear) {
            $hsYears[$studyYear->numeric()] = sprintf(
                _('grade %d (expected graduation in %d)'),
                $studyYear->numeric(),
                $contestYear->getGraduationYear($studyYear)
            );
        }
        $primaryYears = [];
        foreach (StudyYear::getPrimarySchoolCases() as $studyYear) {
            $primaryYears[$studyYear->numeric()] = sprintf(
                _('grade %d (expected graduation in %d)'),
                $studyYear->numeric(),
                $contestYear->getGraduationYear($studyYear)
            );
        }
        /** @phpstan-ignore-next-line */
        return [
            _('high school') => $hsYears,
            _('primary school') => $primaryYears,
        ];
    }

    protected function createHtmlValue(Model $model): Html
    {
        return (new StringPrinter())($model->{$this->modelAccessKey});
    }
}
