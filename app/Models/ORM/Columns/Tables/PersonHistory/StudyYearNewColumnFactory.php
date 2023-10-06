<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\PersonHistory;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\Models\StudyYear;
use Fykosak\NetteORM\Model;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<PersonHistoryModel,ContestYearModel|string>
 */
class StudyYearNewColumnFactory extends ColumnFactory
{
    public const FLAG_HS = 'HS';
    public const FLAG_PS = 'PS';
    public const FLAG_ALL = 'ALL';

    /**
     * @throws \InvalidArgumentException
     */
    protected function createFormControl(...$args): BaseControl
    {
        /**
         * @var string|null $flag
         */
        [$contestYear, $flag] = $args;
        if (!$contestYear instanceof ContestYearModel) {
            throw new \InvalidArgumentException();
        }
        if (!isset($flag)) {
            throw new \InvalidArgumentException();
        }
        $control = new SelectBox($this->getTitle());
        $control->setItems($this->createOptions($contestYear, $flag));
        $control->setOption('description', $this->getDescription());
        $control->setPrompt(_('Choose study year'));
        return $control;
    }

    /**
     * @phpstan-return array<string,array<string,string>>
     */
    private function createOptions(ContestYearModel $contestYear, string $flag): array
    {
        switch ($flag) {
            case self::FLAG_HS:
                return array_merge(
                    $this->addHighSchoolCases($contestYear),
                    $this->addPrimarySchoolCases($contestYear)
                );
            case self::FLAG_PS:
                return $this->addHighSchoolCases($contestYear);
            case self::FLAG_ALL:
                return array_merge(
                    $this->addPrimarySchoolCases($contestYear),
                    $this->addHighSchoolCases($contestYear),
                    $this->addOtherCases()
                );
            default:
                throw new \InvalidArgumentException($flag);
        }
    }

    /**
     * @phpstan-return array<string,array<string,string>>
     */
    private function addPrimarySchoolCases(ContestYearModel $contestYear): array
    {
        $years = [];
        foreach (StudyYear::getPrimarySchoolCases() as $studyYear) {
            $years[$studyYear->value] = sprintf(
                _('grade %d (expected graduation in %d)'),
                $studyYear->numeric(),
                $studyYear->getGraduationYear($contestYear->ac_year)
            );
        }
        return [_('primary school') => $years];
    }

    /**
     * @phpstan-return array<string,array<string,string>>
     */
    private function addHighSchoolCases(ContestYearModel $contestYear): array
    {
        $years = [];
        foreach (StudyYear::getHighSchoolCases() as $studyYear) {
            $years[$studyYear->value] = sprintf(
                _('grade %d (expected graduation in %d)'),
                $studyYear->numeric(),
                $studyYear->getGraduationYear($contestYear->ac_year)
            );
        }
        return [_('high school') => $years];
    }

    /**
     * @phpstan-return array<string,array<string,string>>
     */
    private function addOtherCases(): array
    {
        $years = [];
        foreach ([StudyYear::from(StudyYear::UniversityAll), StudyYear::from(StudyYear::None)] as $studyYear) {
            $years[$studyYear->value] = $studyYear->label();
        }
        return [_('graduated & others') => $years];
    }

    /**
     * @param PersonHistoryModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        return $model->study_year_new->badge();
    }
}

/*
 * update person_history set study_year_new = CONCAT(IF(study_year<5,'H_','P_'),study_year)
 * where study_year IS NOT NULL;

update person_history set study_year_new = 'NONE' where study_year IS NULL AND school_id IS NULL;

update person_history set study_year_new = 'U_ALL' where study_year IS NULL AND school_id IS NOT NULL;
 */
