<?php

namespace FKSDB\Components\Forms\Factories\PersonHistory;


use Nette\Forms\Controls\SelectBox;

class StudyYearField extends SelectBox {

    /**
     * @var integer
     */
    private $acYear;
    /**
     * @var \YearCalculator
     */
    private $yearCalculator;

    public function __construct(\YearCalculator $yearCalculator, $acYear) {

        parent::__construct(_('Ročník'));
        $this->acYear = $acYear;
        $this->yearCalculator = $yearCalculator;
        $this->setItems($this->createOptions());
        $this->setOption('description', _('Kvůli zařazení do kategorie.'));
        $this->setPrompt(_('Zvolit ročník'));

    }

    private function createOptions() {
        $hsYears = [];
        foreach (range(1, 4) as $study_year) {
            $hsYears[$study_year] = sprintf(_('%d. ročník (očekávaný rok maturity %d)'),
                $study_year,
                $this->yearCalculator->getGraduationYear($study_year, $this->acYear));
        }

        $primaryYears = [];
        foreach (range(6, 9) as $study_year) {
            $primaryYears[$study_year] = sprintf(_('%d. ročník (očekávaný rok maturity %d)'),
                $study_year,
                $this->yearCalculator->getGraduationYear($study_year, $this->acYear));
        }

        return [
            _('střední škola') => $hsYears,
            _('základní škola nebo víceleté gymnázium') => $primaryYears,
        ];
    }
}
