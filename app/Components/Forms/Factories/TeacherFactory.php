<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\ModelContainer;
use ModelContest;
use Nette\Forms\ControlGroup;
use Nette\Forms\Form;
use ServicePerson;
use YearCalculator;

/**
 *
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TeacherFactory {

    public function createTeacher() {
        $container = new ModelContainer();

        $container->addText('since', _('Since year'));

        $container->addText('until', _('Until year'));

        $container->addText('note', _('Note'))
            ->addRule(Form::MAX_LENGTH, null, 255);
        return $container;
    }
}
