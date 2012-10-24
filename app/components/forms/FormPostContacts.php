<?php

use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer as IComponentContainer;
use Nette\Forms\Container as FormContainer;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class FormPostContacts extends Form {

    public function __construct(IComponentContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);

        $this->addText('display_name', 'Jméno')->setDisabled();

        $texts = $this->addDynamic('post_contacts', array($this, 'appendAddressDynamic'), 1);
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
        $container->addText('street', 'Ulice');
        $container->addText('house_nr', 'Č P/O');
        $container->addText('city', 'Město')
                ->addRule(Form::FILLED, 'Adresa musí mít vyplněné alespoň město.');
        $container->addText('postal_code', 'PSČ')
                ->addRule(Form::MAX_LENGTH, null, 5);
        $countries = $container->addSelect('country_iso', 'Stát');

        $countries->setItems($serviceCountry->getTable()->order('name_cs')->fetchPairs('country_iso', 'name_cs'));
    }

}
