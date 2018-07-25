<?php

namespace FKSDB\Components\Forms\Factories;

use FKS\Localization\GettextTranslator;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;

/**
 * Class PersonHistoryFactory
 * @package FKSDB\Components\Forms\Factories
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PersonHistoryFactory {
    /**
     *
     * @var GettextTranslator
     */
    private $translator;
    /**
     * @var SchoolFactory
     */
    private $schoolFactory;

    /**
     * @var \YearCalculator
     */
    private $yearCalculator;

    public function __construct(GettextTranslator $translator, SchoolFactory $factorySchool, \YearCalculator $yearCalculator) {
        $this->translator = $translator;
        $this->schoolFactory = $factorySchool;;
    }

    private function createStudyYear($acYear) {
        $studyYear = new SelectBox(_('Ročník'));

        $hsYears = [];
        foreach (range(1, 4) as $study_year) {
            $hsYears[$study_year] = sprintf(_('%d. ročník (očekávaný rok maturity %d)'),
                $study_year,
                $this->yearCalculator->getGraduationYear($study_year, $acYear));
        }

        $primaryYears = [];
        foreach (range(6, 9) as $study_year) {
            $primaryYears[$study_year] = sprintf(_('%d. ročník (očekávaný rok maturity %d)'),
                $study_year,
                $this->yearCalculator->getGraduationYear($study_year, $acYear));
        }

        $studyYear->setItems([
            _('střední škola') => $hsYears,
            _('základní škola nebo víceleté gymnázium') => $primaryYears,
        ])->setOption('description', _('Kvůli zařazení do kategorie.'))
            ->setPrompt(_('Zvolit ročník'));

        return $studyYear;
    }

    private function createSchoolId() {
        return $this->schoolFactory->createSchoolSelect(SchoolFactory::SHOW_UNKNOWN_SCHOOL_HINT);
    }

    private function createClass() {
        return (new TextInput(_('Třída')))
            ->addRule(Form::MAX_LENGTH, null, 16);
    }

    /**
     * @param string $fieldName
     * @return \FKS\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox|\Nette\Forms\Controls\BaseControl
     */
    public function createField($fieldName, $acYear) {
        switch ($fieldName) {
            case 'class':
                return $this->createClass();
            case 'school_id':
                return $this->createSchoolId();
            case 'study_year':
                return $this->createStudyYear($acYear);
            default:
                throw new InvalidArgumentException();
        }

    }

}
