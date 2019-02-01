<?php

namespace FKSDB\Components\Controls\Fyziklani;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelSubmit;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Forms\Controls\RadioList;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Templating\FileTemplate;

/**
 * Class EditSubmitControl
 * @package FKSDB\Components\Controls\Fyziklani
 * @property FileTemplate $template
 */
class EditSubmitControl extends Control {
    /**
     * @var \ServiceFyziklaniSubmit
     */
    private $serviceFyziklaniSubmit;
    /**
     * @var ModelSubmit
     */
    private $editSubmit;
    /**
     * @var ModelEvent
     */
    private $event;

    public function __construct(ModelEvent $event, \ServiceFyziklaniSubmit $serviceFyziklaniSubmit) {
        parent::__construct();
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
        $this->event = $event;
    }

    public function getForm(): Form {
        /**
         * @var $control FormControl
         */
        $control = $this->getComponent('form');
        return $control->getForm();
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function setSubmit(int $id) {
        $this->editSubmit = $this->serviceFyziklaniSubmit->findByPrimary($id);

        if (!$this->editSubmit) {
            throw new BadRequestException(_('Neexistující submit.'), 404);
        }

        $team = $this->editSubmit->getTeam();
        if (!$team->hasOpenSubmit()) {
            throw new BadRequestException(_('Bodování tohoto týmu je uzavřené.'));
        }
        $this->template->fyziklani_submit_id = $this->editSubmit ? true : false;
        /**
         * @var $control FormControl
         */
        $control = $this->getComponent('form');
        $control->getForm()->setDefaults([
            'team_id' => $this->editSubmit->e_fyziklani_team_id,
            'task' => $this->editSubmit->getTask()->label,
            'points' => $this->editSubmit->points,
            'team' => $this->editSubmit->getTeam()->name,
        ]);
    }

    /**
     * @return RadioList
     */
    private function createPointsField(): RadioList {
        $field = new RadioList(_('Počet bodů'));
        $items = [];
        foreach ($this->event->getFyziklaniGameSetup()->getAvailablePoints() as $points) {
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
     * @return FormControl
     */
    protected function createComponentForm(): FormControl {
        $control = new FormControl();
        $form = $control->getForm();

        $form->addComponent($this->createTeamField(), 'team');
        $form->addComponent($this->createTeamIdField(), 'team_id');
        $form->addComponent($this->createTaskField(), 'task');
        $form->addComponent($this->createPointsField(), 'points');
        $form->addSubmit('send', _('Uložit'));
        $form->onSuccess[] = function (Form $form) {
            $this->editFormSucceeded($form);
        };
        return $control;
    }

    /**
     * @param Form $form
     */
    private function editFormSucceeded(Form $form) {
        $values = $form->getValues();

        $submit = $this->editSubmit;
        $this->serviceFyziklaniSubmit->updateModel($submit, [
            'points' => $values->points,
            /* ugly, exclude previous value of `modified` from query
             * so that `modified` is set automatically by DB
             * see https://dev.mysql.com/doc/refman/5.5/en/timestamp-initialization.html
             */
            'modified' => null
        ]);
        $this->serviceFyziklaniSubmit->save($submit);
        $this->getPresenter()->flashMessage(\sprintf(_('Body byly změněny: Tým "%s", body %d.'), $submit->getTeam()->name, $submit->points), 'success');

    }

    public function render() {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'EditSubmitControl.latte');
        $this->template->render();
    }
}
