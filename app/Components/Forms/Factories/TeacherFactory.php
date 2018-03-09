<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\ModelContainer;
use JanTvrdik\Components\DatePicker;
use Nette\Forms\Form;

/**
 *
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TeacherFactory {

    public function createTeacher() {
        $container = new ModelContainer();
        $container->addComponent(new DatePicker(_('Since year')), 'since');
        $container->addComponent(new DatePicker(_('Until year')), 'until');
        $container->addText('note', _('Note'))
            ->addRule(Form::MAX_LENGTH, null, 255);
        return $container;
    }
}
