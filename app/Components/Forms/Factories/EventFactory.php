<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Services\ServiceEventType;
use Nette\Forms\Controls\BaseControl;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class EventFactory extends SingleReflectionFactory {

    /**
     * @var ServiceEventType
     */
    private $serviceEventType;

    /**
     * EventFactory constructor.
     * @param ServiceEventType $serviceEventType
     * @param TableReflectionFactory $tableReflectionFactory
     */
    public function __construct(ServiceEventType $serviceEventType, TableReflectionFactory $tableReflectionFactory) {
        parent::__construct($tableReflectionFactory);
        $this->serviceEventType = $serviceEventType;
    }

    /**
     * @param ModelContest $contest
     * @return ModelContainer
     * @throws \Exception
     */
    public function createEvent(ModelContest $contest): ModelContainer {
        $container = new ModelContainer();
        foreach (['event_type_id', 'event_year', 'name', 'begin', 'end', 'registration_begin', 'registration_end', 'report', 'parameters'] as $field) {
            $control = $this->createField($field, $contest);
            $container->addComponent($control, $field);
        }
        return $container;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_EVENT;
    }

    /**
     * @param string $fieldName
     * @param array $args
     * @return BaseControl
     * @throws \Exception
     */
    public function createField(string $fieldName, ...$args): BaseControl {
        list ($contest) = $args;
        switch ($fieldName) {
            case 'event_type_id':
                return $this->loadFactory($fieldName)->createField($contest);
            default:
                return parent::createField($fieldName);
        }
    }
}
