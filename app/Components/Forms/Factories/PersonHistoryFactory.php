<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Factories\PersonHistory\ClassField;
use FKSDB\Components\Forms\Factories\PersonHistory\StudyYearField;
use Nette\Forms\Controls\BaseControl;
use Nette\InvalidArgumentException;

/**
 * Class PersonHistoryFactory
 * @package FKSDB\Components\Forms\Factories
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PersonHistoryFactory {

    /**
     * @var SchoolFactory
     */
    private $schoolFactory;

    /**
     * @var \YearCalculator
     */
    private $yearCalculator;

    public function __construct(SchoolFactory $factorySchool, \YearCalculator $yearCalculator) {
        $this->schoolFactory = $factorySchool;
        $this->yearCalculator = $yearCalculator;
    }

    /**
     * @param string $fieldName
     * @param integer $acYear
     * @return BaseControl | \IReactField
     */
    public function createField($fieldName, $acYear) {
        switch ($fieldName) {
            case 'class':
                return new ClassField();
            case 'school_id':
                return $this->schoolFactory->createSchoolSelect();
            case 'study_year':
                return new StudyYearField($this->yearCalculator, $acYear);
            default:
                throw new InvalidArgumentException();
        }

    }

    public function createReactField($fieldName, $acYear) {
        return $this->createField($fieldName, $acYear)->getReactDefinition();
    }

}
