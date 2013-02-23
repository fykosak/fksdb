<?php

use Nette\Application\UI\Form;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class FormContainerAddress extends FormContainerModel {

    /**
     * @var ServiceCountry
     */
    private $serviceCountry;

    public function __construct(ServiceCountry $serviceCountry) {
        parent::__construct();

        $this->serviceCountry = $serviceCountry;
        $this->initFields();
    }

    private function initFields() {
        $this->addText('first_row', 'První řádek')
                ->setOption('description', 'První volitelný řádek adresy (např. bytem u)');

        $this->addText('second_row', 'Druhý řádek')
                ->setOption('description', 'Druhý volitelný řádek adresy (požití zřídka)');


        $this->addText('target', 'Místo')
                ->addRule(Form::FILLED, 'Adresa musí vyplněné místo.')
                ->setOption('description', 'Nejčastěji ulice a číslo, ale třeba i P. O. Box.');

        $this->addText('city', 'Město')
                ->addRule(Form::FILLED, 'Adresa musí mít vyplněné město.');


        $this->addText('postal_code', 'PSČ')
                ->addRule(Form::MAX_LENGTH, null, 5)
                ->setOption('description', 'Bez mezer');


        $countries = $this->addSelect('country_iso', 'Stát');

        $countries->setItems($this->serviceCountry->getTable()->order('name_cs')->fetchPairs('country_iso', 'name_cs'));

        $this->addHidden('address_id');
    }

    public function setDefaults($values, $erase = FALSE) {
        if ($values instanceof Nette\Database\Table\ActiveRow) { //assert its from address table
            $address = $values;
            $values = $address->toArray();
            $values['country_iso'] = $address->region_id ? $address->region->country_iso : null;
        }

        parent::setDefaults($values, $erase);
    }

}
