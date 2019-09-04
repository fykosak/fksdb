<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\ORM\DbNames;
use JanTvrdik\Components\DatePicker;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

/**
 *
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TeacherFactory extends SingleReflectionFactory {
    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_TEACHER;
    }

    /**
     * @return ModelContainer
     * @throws \Exception
     */
    public function createTeacher(): ModelContainer {
        return $this->createContainer(['state', 'since', 'until', 'number_brochures', 'note']);
    }
}
