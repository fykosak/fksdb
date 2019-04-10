<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Factories\PersonHistory\StudyYearField;
use FKSDB\ORM\DbNames;
use Nette\Forms\IControl;

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
     * @var \FKSDB\YearCalculator
     */
    private $yearCalculator;
    /**
     * @var TableReflectionFactory
     */
    private $tableReflectionFactory;

    /**
     * PersonHistoryFactory constructor.
     * @param TableReflectionFactory $tableReflectionFactory
     * @param SchoolFactory $factorySchool
     * @param \FKSDB\YearCalculator $yearCalculator
     */
    public function __construct(TableReflectionFactory $tableReflectionFactory, SchoolFactory $factorySchool, \FKSDB\YearCalculator $yearCalculator) {
        $this->schoolFactory = $factorySchool;
        $this->yearCalculator = $yearCalculator;
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    /**
     * @param string $fieldName
     * @param integer $acYear
     * @return IControl
     * @throws \Exception
     */
    public function createField($fieldName, $acYear): IControl {
        switch ($fieldName) {
            case 'school_id':
                return $this->schoolFactory->createSchoolSelect();
            case 'study_year':
                return new StudyYearField($this->yearCalculator, $acYear);
            default:
                return $this->tableReflectionFactory->createField(DbNames::TAB_PERSON_HISTORY, 'class');
        }
    }
}
