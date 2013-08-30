<?php

use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer as IComponentContainer;
use Nette\Forms\Container as FormContainer;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class FormSchool extends Form {

    const SCHOOL = 'school';
    const ADDRESS = 'address';

    public function __construct(ServiceCountry $serviceCountry, IComponentContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);

        $school = $this->addContainer(self::SCHOOL);
        $address = $this->addContainer(self::ADDRESS);

        $this->addSchool($school);
        FormPostContacts::appendAddress($address, $serviceCountry);
    }

    private function addSchool(FormContainer $container) {

        $container->addText('name_full', 'Plný název')
                ->setOption('description', 'Úplný nezkrácený název školy.');

        $container->addText('name', 'Název')
                ->addRule(Form::FILLED, 'Název je povinný.')
                ->setOption('description', 'Název na obálku.');

        $container->addText('name_abbrev', 'Zkrácený název')
                ->addRule(Form::FILLED, 'Zkrácený název je povinný.')
                ->setOption('description', 'Název krátký do výsledkovky.');

        $container->addText('email', 'Kontaktní e-mail')
                ->addCondition(Form::FILLED)
                ->addRule(Form::EMAIL);

        $container->addText('ic', 'IČ')
                ->addRule(Form::MAX_LENGTH, 'Délka IČ je omezena na 8 znaků.', 8);

        $container->addText('izo', 'IZO')
                ->addRule(Form::MAX_LENGTH, 'Délka IZO je omezena na 32 znaků.', 32);

        $container->addCheckbox('active', 'Aktivní záznam')
                ->setDefaultValue(true);

        $container->addText('note', 'Poznámka');
    }

}
