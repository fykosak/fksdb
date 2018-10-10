<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Factories\PersonHistory\ClassField;
use FKSDB\Components\Forms\Factories\PersonHistory\StudyYearField;
use FKSDB\Components\Forms\TableReflection\TableReflectionFactory;
use Nette\ArgumentOutOfRangeException;
use Nette\Forms\Controls\BaseControl;
use Nette\InvalidArgumentException;

/**
 * Class PersonHistoryFactory
 * @package FKSDB\Components\Forms\Factories
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PersonHistoryFactory extends TableReflectionFactory {

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

    public function createField(string $fieldName, array $data = []): BaseControl {
        switch ($fieldName) {
            case 'class':
                return new ClassField();
            case 'school_id':
                return $this->schoolFactory->createSchoolSelect();
            case 'study_year':
                if (!in_array('acYear', $data)) {
                    throw new ArgumentOutOfRangeException('parameter data musí obsahovať property "acYear"');
                }
                return new StudyYearField($this->yearCalculator, $data['acYear']);
            default:
                throw new InvalidArgumentException();
        }
    }
}
