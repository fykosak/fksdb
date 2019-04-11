<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Services\ServiceEventType;
use Nette\Forms\ControlGroup;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Form;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class EventFactory {

    const SHOW_UNKNOWN_SCHOOL_HINT = 0x1;

    /**
     * @var \FKSDB\ORM\Services\ServiceEventType
     */
    private $serviceEventType;
    /**
     * @var TableReflectionFactory
     */
    private $tableReflectionFactory;

    /**
     * EventFactory constructor.
     * @param ServiceEventType $serviceEventType
     * @param TableReflectionFactory $tableReflectionFactory
     */
    function __construct(ServiceEventType $serviceEventType, TableReflectionFactory $tableReflectionFactory) {
        $this->serviceEventType = $serviceEventType;
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    /**
     * @param ModelContest $contest
     * @return ModelContainer
     * @throws \Exception
     */
    public function createEvent(ModelContest $contest): ModelContainer {
        $container = new ModelContainer();

        $type = $this->createEventType($contest);
        $type->addRule(Form::FILLED, _('%label je povinný.'));

        $container->addComponent($type, 'event_type_id');

        foreach (['event_year', 'name', 'begin', 'end', 'registration_begin', 'registration_end', 'report', 'parameters'] as $field) {
            $control = $this->tableReflectionFactory->createField(DbNames::TAB_EVENT, $field);
            $container->addComponent($control, $field);
        }
        return $container;
    }

    /**
     * @param ModelContest $contest
     * @return SelectBox
     */
    public function createEventType(ModelContest $contest): SelectBox {
        $element = new SelectBox(_('Typ akce'));

        $types = $this->serviceEventType->getTable()->where('contest_id', $contest->contest_id)->fetchPairs('event_type_id', 'name');
        $element->setItems($types);
        $element->setPrompt(_('Zvolit typ'));

        return $element;
    }

}
