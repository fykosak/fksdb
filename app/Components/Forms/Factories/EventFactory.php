<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelContest;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class EventFactory extends SingleReflectionFactory {
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

    protected function getTableName(): string {
        return DbNames::TAB_EVENT;
    }
}
