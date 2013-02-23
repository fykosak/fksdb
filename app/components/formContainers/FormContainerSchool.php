<?php

use Nette\Application\UI\Form;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class FormContainerSchool extends \Nette\Forms\Container {

    public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);
        $this->initFields();
    }

    private function initFields() {
        $this->addText('name_full', 'Plný název')
                ->setOption('description', 'Úplný nezkrácený název školy.');

        $this->addText('name', 'Název')
                ->addRule(Form::FILLED, 'Název je povinný.')
                ->setOption('description', 'Název na obálku.');

        $this->addText('name_abbrev', 'Zkrácený název')
                ->addRule(Form::FILLED, 'Zkrácený název je povinný.')
                ->setOption('description', 'Název krátký do výsledkovky.');

        $this->addText('email', 'Kontaktní e-mail')
                ->addCondition(Form::FILLED)
                ->addRule(Form::EMAIL);

        $this->addText('ic', 'IČ')
                ->addRule(Form::MAX_LENGTH, 'Délka IČ je omezena na 8 znaků.', 8);

        $this->addText('izo', 'IZO')
                ->addRule(Form::MAX_LENGTH, 'Délka IZO je omezena na 32 znaků.', 32);

        $this->addCheckbox('active', 'Aktivní záznam')
                ->setDefaultValue(true);

        $this->addText('note', 'Poznámka');

        $this->addHidden('school_id');
    }

}
