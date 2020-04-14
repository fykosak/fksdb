<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\ORM\DbNames;
use Nette\Forms\Controls\BaseControl;

/**
 * Class PersonHistoryFactory
 * @package FKSDB\Components\Forms\Factories
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PersonHistoryFactory extends SingleReflectionFactory {

    /**
     * @var SchoolFactory
     */
    private $schoolFactory;

    /**
     * PersonHistoryFactory constructor.
     * @param TableReflectionFactory $tableReflectionFactory
     * @param SchoolFactory $factorySchool
     */
    public function __construct(TableReflectionFactory $tableReflectionFactory, SchoolFactory $factorySchool) {
        parent::__construct($tableReflectionFactory);
        $this->schoolFactory = $factorySchool;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_PERSON_HISTORY;
    }

    /**
     * @param string $fieldName
     * @param array $args
     * @return BaseControl
     * @throws \Exception
     */
    public function createField(string $fieldName, ...$args): BaseControl {
        switch ($fieldName) {
            case 'school_id':
                return $this->schoolFactory->createSchoolSelect();
            case 'study_year':
                list($acYear) = $args;
                return $this->loadFactory($fieldName)->createField($acYear);
            default:
                return parent::createField($fieldName);
        }
    }
}
