<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Controls\TaskCodeInput;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use ModelEvent;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Nette\Forms\Controls\RadioList;
use Nette\Forms\Controls\TextInput;

class BrawlFactory {

    private $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    private function createPointsField(ModelEvent $event) {
        $field = new RadioList(_('Počet bodů'));
        $items = [];
        foreach ($event->getParameter('availablePoints') as $points) {
            $items[$points] = $points;
        }
        $field->setItems($items);
        $field->setRequired();
        return $field;
    }

    private function createTaskCodeField($teams = [], $tasks = []) {

        $field = new TaskCodeInput(_('Kód úlohy'));
        $field->setTasks($tasks);
        $field->setTeams($teams);
        $field->setRequired();

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

    public function createEntryForm(ModelEvent $event, $teams, $tasks) {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());
        $form->addComponent($this->createTaskCodeField($teams, $tasks), 'taskCode');
        foreach ($event->getParameter('availablePoints') as $points) {
            $label = ($points == 1) ? _('bod') : (($points < 5) ? _('body') : _('bodů'));
            $form->addSubmit('points' . $points, _($points . ' ' . $label))
                ->setAttribute('class', 'btn-' . $points . '-points');
        }
        $form->addProtection(_('Vypršela časová platnost formuláře. Odešlete jej prosím znovu.'));
        return $form;
    }

    public function createEditForm(ModelEvent $event) {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());
        $form->addComponent($this->createTeamField(), 'team');
        $form->addComponent($this->createTeamIDField(), 'team_id');
        $form->addComponent($this->createTaskField(), 'task');
        $form->addComponent($this->createPointsField($event), 'points');
        $form->addSubmit('send', _('Uložit'));
        return $form;
    }
}
