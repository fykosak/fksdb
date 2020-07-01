<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\ORM\DbNames;
use Nette\Forms\Controls\BaseControl;

/**
 * Class PersonHistoryFactory
 * *
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PersonHistoryFactory extends AbstractSingleReflectionFormFactory {

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
     * @param string $fieldName
     * @param array $args
     * @return BaseControl
     * @throws \Exception
     */
    public function createField(string $tableName, string $fieldName, ...$args): BaseControl {
        switch ($fieldName) {
            case 'school_id':
                return $this->schoolFactory->createSchoolSelect();
            default:
                return parent::createField($tableName, $fieldName, ...$args);
        }
    }
}
