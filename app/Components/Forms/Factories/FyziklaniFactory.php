<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\React\Fyziklani\FyziklaniComponentsFactory;
use FKSDB\Components\React\Fyziklani\TaskCodeInput;
use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniGameSetup;
use Nette\Forms\Controls\RadioList;
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
    /**
     * @var FyziklaniComponentsFactory
     */
    private $fyziklaniComponentsFactory;

    public function __construct(ServiceFyziklaniTeam $serviceFyziklaniTeam, ServiceFyziklaniTask $serviceFyziklaniTask, FyziklaniComponentsFactory $fyziklaniComponentsFactory) {
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->fyziklaniComponentsFactory = $fyziklaniComponentsFactory;
    }

    /**
     * @param ModelFyziklaniGameSetup $gameSetup
     * @return RadioList
     */
    private function createPointsField(ModelFyziklaniGameSetup $gameSetup): RadioList {
        $field = new RadioList(_('Počet bodů'));
        $items = [];
        foreach ($gameSetup->getAvailablePoints() as $points) {
            $items[$points] = $points;
        }
        $field->setItems($items);
        $field->setRequired();
        return $field;
    }

    /**
     * @return TextInput
     */
    private function createTeamField(): TextInput {
        $field = new TextInput(_('Tým'));
        $field->setDisabled(true);
        return $field;
    }

    /**
     * @return TextInput
     */
    private function createTeamIdField(): TextInput {
        $field = new TextInput(_('Id týmu'));
        $field->setDisabled(true);
        return $field;
    }

    /**
     * @return TextInput
     */
    private function createTaskField(): TextInput {
        $field = new TextInput(_('Úloha'));
        $field->setDisabled(true);
        return $field;
    }

    /**
     * @param ModelEvent $event
     * @return TaskCodeInput
     */
    public function createEntryForm(ModelEvent $event): TaskCodeInput {
        return $this->fyziklaniComponentsFactory->createTaskCodeInput($event);
    }

    /**
     * @param ModelFyziklaniGameSetup $gameSetup
     * @return FormControl
     */
    public function createEntryQRForm(ModelFyziklaniGameSetup $gameSetup): FormControl {
        $control = new FormControl();
        $form = $control->getForm();
        $form->addText('taskCode')->setAttribute('readonly', true);

        foreach ($gameSetup->getAvailablePoints() as $points) {
            $label = ($points == 1) ? _('bod') : (($points < 5) ? _('body') : _('bodů'));
            $form->addSubmit('points' . $points, _($points . ' ' . $label))
                ->setAttribute('class', 'btn-' . $points . '-points')->setDisabled(true);
        }
        $form->addProtection(_('Vypršela časová platnost formuláře. Odešlete jej prosím znovu.'));
        return $control;
    }

    /**
     * @param ModelFyziklaniGameSetup $gameSetup
     * @return FormControl
     */
    public function createEditForm(ModelFyziklaniGameSetup $gameSetup): FormControl {
        $control = new FormControl();
        $form = $control->getForm();

        $form->addComponent($this->createTeamField(), 'team');
        $form->addComponent($this->createTeamIdField(), 'team_id');
        $form->addComponent($this->createTaskField(), 'task');
        $form->addComponent($this->createPointsField($gameSetup), 'points');
        $form->addSubmit('send', _('Uložit'));
        return $control;
    }
}
