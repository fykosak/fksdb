<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnException;
use FKSDB\Components\DatabaseReflection\OmittedControlException;
use FKSDB\Components\Forms\Containers\ModelContainer;

/**
 *
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TeacherFactory extends SingleReflectionFactory {
    /**
     * @return ModelContainer
     * @throws AbstractColumnException
     * @throws OmittedControlException
     */
    public function createTeacher(): ModelContainer {
        return $this->createContainer(['teacher.state', 'teacher.since', 'teacher.until', 'teacher.number_brochures', 'teacher.note']);
    }
}
