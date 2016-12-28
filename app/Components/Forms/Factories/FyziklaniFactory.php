<?php

namespace FKSDB\Components\Forms\Factories;

use \Nette\Forms\Controls\RadioList;
use \Nette\Forms\Controls\TextInput;
use \Nette\DI\Container;
use \Nette\Application\UI\Form;

/**
 *
 *
 * @author Michal Červeňák <miso@fykos.cz>
 */
class FyziklaniFactory {

    private $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    private function createPointsField() {
        $field = new RadioList(_('Počet bodů'));
        $items = [];
        foreach ($this->container->parameters['fyziklani']['availablePoints'] as $v) {
            $items[$v] = $v;
        }
        $field->setItems($items);
        $field->setRequired();
        return $field;
    }

    private function createTaskCodeField() {
        $field = new TextInput(_('Kód úlohy'));
        $field->setRequired();
        $field->addRule(\Nette\Forms\Form::PATTERN,_('Nesprávyn tvar'),'[0-9]{6}[A-Z]{2}[0-9]');
        $field->setAttribute('placeholder','000000XX0');
        return $field;
    }

    private function createTeamField() {
        $field = new TextInput(_('Tým'));
        $field->setDisabled(true);
        return $field;
    }

    private function createTeamIDField() {
        $field = new TextInput(_('Tým ID'));
        $field->setDisabled(true);
        return $field;
    }

    private function createTaskField() {
        $field = new TextInput(_('Úloha'));
        $field->setDisabled(true);
        return $field;
    }

    public function createEntryForm() {
        $form = new Form();
        $form->addComponent($this->createTaskCodeField(),'taskCode');
        $form->addComponent($this->createPointsField(),'points');
        $form->addSubmit('send','Uložit');
        return $form;
    }

    public function createEditForm() {
        $form = new Form();
        $form->addHidden('submit_id',0);
        $form->addComponent($this->createTeamField(),'team');
        $form->addComponent($this->createTeamIDField(),'team_id');
        $form->addComponent($this->createTaskField(),'task');
        $form->addComponent($this->createPointsField(),'points');
        return $form;
    }
}
