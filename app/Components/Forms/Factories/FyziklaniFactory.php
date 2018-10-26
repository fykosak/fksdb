<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Factories\Fyziklani\FyziklaniSubmit\PointsField;
use FKSDB\Components\React\Fyziklani\TaskCodeInput;
use FKSDB\ORM\ModelEvent;
use Nette\Forms\Controls\TextInput;
use ORM\Services\Events\ServiceFyziklaniTeam;
use ServiceFyziklaniTask;

class FyziklaniFactory {
    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;
    /**
     * @var ServiceFyziklaniTask
     */
    private $serviceFyziklaniTask;

    public function __construct(ServiceFyziklaniTeam $serviceFyziklaniTeam, ServiceFyziklaniTask $serviceFyziklaniTask) {
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
    }

    private function createTeamField(): TextInput {
        $field = new TextInput(_('Tým'));
        $field->setDisabled(true);
        return $field;
    }

    private function createTeamIdField(): TextInput {
        $field = new TextInput(_('ID týmu'));
        $field->setDisabled(true);
        return $field;
    }

    private function createTaskField(): TextInput {
        $field = new TextInput(_('Úloha'));
        $field->setDisabled(true);
        return $field;
    }

    public function createEntryForm($eventId): TaskCodeInput {
        $control = new TaskCodeInput($this->serviceFyziklaniTeam, $this->serviceFyziklaniTask, $eventId);
        return $control;
    }

    /**
     * @param \FKSDB\ORM\ModelEvent $event
     * @return FormControl
     */
    public function createEntryQRForm(ModelEvent $event): FormControl {
        $control = new FormControl();
        $form = $control->getForm();
        $form->addText('taskCode')->setAttribute('readonly', true);
        foreach ($event->getParameter('availablePoints') as $points) {
            $label = ($points == 1) ? _('bod') : (($points < 5) ? _('body') : _('bodů'));
            $form->addSubmit('points' . $points, _($points . ' ' . $label))
                ->setAttribute('class', 'btn-' . $points . '-points')->setDisabled(true);
        }
        $form->addProtection(_('Vypršela časová platnost formuláře. Odešlete jej prosím znovu.'));
        return $control;
    }

    public function createEditForm(ModelEvent $event): FormControl {
        $control = new FormControl();
        $form = $control->getForm();

        $form->addComponent($this->createTeamField(), 'team');
        $form->addComponent($this->createTeamIdField(), 'team_id');
        $form->addComponent($this->createTaskField(), 'task');
        $pointsField = new PointsField($event->getParameter('availablePoints'));
        $pointsField->setRequired(true);
        $form->addComponent($pointsField, 'points');
        $form->addSubmit('send', _('Uložit'));
        return $control;
    }
}
