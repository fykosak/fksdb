<?php

namespace FKSDB\Components\Forms\Factories;

use FyziklaniModule\BasePresenter;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use \Nette\Forms\Controls\RadioList;
use \Nette\Forms\Controls\TextInput;
use \Nette\DI\Container;
use \Nette\Application\UI\Form;

class FyziklaniFactory {

    private $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    private function createPointsField($eventID) {
        $field = new RadioList(_('Počet bodů'));
        $items = [];
        foreach ($this->container->parameters[BasePresenter::EVENT_NAME][$eventID]['availablePoints'] as $v) {
            $items[$v] = $v;
        }
        $field->setItems($items);
        $field->setRequired();
        return $field;
    }

    private function createTaskCodeField() {
        $field = new TextInput(_('Kód úlohy'));
        $field->setRequired();
        $field->addRule(Form::PATTERN, _('Nesprávný tvar.'), '[0-9]{6}[A-Z]{2}[0-9]');
        $field->setAttribute('placeholder', '000000XX0');
        return $field;
    }

    private function createTeamField() {
        $field = new TextInput(_('Tým'));
        $field->setDisabled(true);
        return $field;
    }

    private function createTeamIDField() {
        $field = new TextInput(_('ID týmu'));
        $field->setDisabled(true);
        return $field;
    }

    private function createTaskField() {
        $field = new TextInput(_('Úloha'));
        $field->setDisabled(true);
        return $field;
    }

    public function createEntryForm($eventID) {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());
        $form->addComponent($this->createTaskCodeField(), 'taskCode');
        //$form->addComponent($this->createPointsField(),'points');
        //$form->addSubmit('send',_('Uložit'));
        foreach ($this->container->parameters[BasePresenter::EVENT_NAME][$eventID]['availablePoints'] as $points) {
            $label = ($points == 1) ? _('bod') : (($points < 5) ? _('body') : _('bodů'));
            $form->addSubmit('points' . $points, _($points . ' ' . $label))
                ->setAttribute('class', 'btn-' . $points . '-points');
        }
        $form->addProtection(_('Vypršela časová platnost formuláře. Odešlete jej prosím znovu.'));
        return $form;
    }

    public function createEditForm($eventID) {
        $form = new Form();
        $form->addHidden('submit_id', 0);
        $form->setRenderer(new BootstrapRenderer());
        $form->addComponent($this->createTeamField(), 'team');
        $form->addComponent($this->createTeamIDField(), 'team_id');
        $form->addComponent($this->createTaskField(), 'task');
        $form->addComponent($this->createPointsField($eventID), 'points');
        $form->addSubmit('send', _('Uložit'));
        return $form;
    }
}
