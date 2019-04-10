<?php

namespace FKSDB\Components\Forms\Factories\PersonHistory;


use FKSDB\YearCalculator;
use Nette\Forms\Controls\SelectBox;

/**
 * Class StudyYearField
 * @package FKSDB\Components\Forms\Factories\PersonHistory
 */
class StudyYearField extends SelectBox {

    /**
     * @var integer
     */
    private $acYear;
    /**
     * @var YearCalculator
     */
    private $yearCalculator;

    /**
     * StudyYearField constructor.
     * @param YearCalculator $yearCalculator
     * @param $acYear
     */
    public function __construct(YearCalculator $yearCalculator, $acYear) {

        parent::__construct(_('Ročník'));
        $this->acYear = $acYear;
        $this->yearCalculator = $yearCalculator;
        $this->setItems($this->createOptions());
        $this->setOption('description', _('Kvůli zařazení do kategorie.'));
        $this->setPrompt(_('Zvolit ročník'));

    }

    /**
     * @return array
     */
    private function createOptions() {
        $hsYears = [];
        foreach (range(1, 4) as $studyYear) {
            $hsYears[$studyYear] = sprintf(_('%d. ročník (očekávaný rok maturity %d)'),
                $studyYear,
                $this->yearCalculator->getGraduationYear($studyYear, $this->acYear));
        }

        $primaryYears = [];
        foreach (range(6, 9) as $studyYear) {
            $primaryYears[$studyYear] = sprintf(_('%d. ročník (očekávaný rok maturity %d)'),
                $studyYear,
                $this->yearCalculator->getGraduationYear($studyYear, $this->acYear));
        }

        return [
            _('střední škola') => $hsYears,
            _('základní škola nebo víceleté gymnázium') => $primaryYears,
        ];
    }
}
