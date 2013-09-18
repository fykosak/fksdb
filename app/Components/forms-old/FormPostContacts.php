<?php

use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer as IComponentContainer;
use Nette\Forms\Container as FormContainer;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class FormPostContacts extends Form {

    const POST_CONTACTS = 'post_contacts';

    public function __construct(IComponentContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);

        $this->addText('other_name', 'Jméno')
                ->setDisabled();
        $this->addText('family_name', 'Příjmení')
                ->setDisabled();

        $texts = $this->addDynamic(self::POST_CONTACTS, array($this, 'appendAddressDynamic'), 1);
        $texts->addSubmit('add', 'Přidat další adresu')->addCreateOnClick();
        //TODO get region from PSČ
    }

    public function appendAddressDynamic(FormContainer $container) {
        self::appendAddress($container, $this->getPresenter()->getService('ServiceCountry'));

        $type = $container->addSelect('type', 'Druh adresy', ModelPostContact::$types);
        $type->setDefaultValue(ModelPostContact::TYPE_PERMANENT);

        $container->addSubmit('remove', 'Odebrat')->addRemoveOnClick();
    }

    public static function appendAddress(FormContainer $container, ServiceCountry $serviceCountry) {

        $container->addText('first_row', 'První řádek')
                ->setOption('description', 'První volitelný řádek adresy (např. bytem u)');

        $container->addText('second_row', 'Druhý řádek')
                ->setOption('description', 'Druhý volitelný řádek adresy (požití zřídka)');


        $container->addText('target', 'Místo')
                ->addRule(Form::FILLED, 'Adresa musí vyplněné místo.')
                ->setOption('description', 'Nejčastěji ulice a číslo, ale třeba i P. O. Box.');

        $container->addText('city', 'Město')
                ->addRule(Form::FILLED, 'Adresa musí mít vyplněné město.');


        $container->addText('postal_code', 'PSČ')
                ->addRule(Form::MAX_LENGTH, null, 5)
                ->setOption('description', 'Bez mezer');


        $countries = $container->addSelect('country_iso', 'Stát');

        $countries->setItems($serviceCountry->getTable()->order('name_cs')->fetchPairs('country_iso', 'name_cs'));
    }

}
