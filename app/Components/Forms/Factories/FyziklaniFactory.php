<?php

namespace FKSDB\Components\Forms\Factories;

use \Nette\Forms\Controls\RadioList;
use \Nette\Forms\Controls\TextInput;

/**
 * Description of PointsFactory
 *
 * @author miso
 */
class FyziklaniFactory {

    public function __construct() {
        ;
    }

    public function createPointsField() {
        $field = new RadioList(_('Počet bodů'));
        $field->setItems(array(5 => 5,3 => 3,2 => 2,1 => 1));
        $field->setRequired();
        return $field;
    }

    public function createTaskCodeField() {
        $field = new TextInput(_('Kód úlohy'));
        $field->setRequired();
        $field->addRule(\Nette\Forms\Form::PATTERN,'Nesprávyn tvar','[0-9]{5}[A-Z]{2}[0-9]');
        $field->setAttribute('placeholder','00000XX0');
        return $field;
    }

    public function createEntryForm() {
        $form = new \Nette\Application\UI\Form();        
        $form->addComponent($this->createTaskCodeField(),'taskCode');
        $form->addComponent($this->createPointsField(),'points');
        $form->addSubmit('send','Uložit');
        return $form;
    }

    //put your code here
}
