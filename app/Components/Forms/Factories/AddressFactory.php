<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\AddressContainer;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Forms\ControlGroup;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\RadioList;
use ServiceAddress;
use ServiceCountry;
use ServiceRegion;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class AddressFactory {

    /**
     * @var ServiceCountry
     */
    private $serviceCountry;

    /**
     * @var ServiceAddress
     */
    private $serviceAddress;

    /**
     * @var ServiceRegion
     */
    private $serviceRegion;

    function __construct(ServiceCountry $serviceCountry, ServiceAddress $serviceAddress, ServiceRegion $serviceRegion) {
        $this->serviceCountry = $serviceCountry;
        $this->serviceAddress = $serviceAddress;
        $this->serviceRegion = $serviceRegion;
    }

    public function createAddress(ControlGroup $group = null) {
        $container = new AddressContainer();
        $this->buildAddress($container, $group);
        return $container;
    }

    /**
     * Appends elements to an existing container.
     * (Created because of KdybyReplicator.)
     * 
     * @param \FKSDB\Components\Forms\Factories\Container $container
     * @param ControlGroup $group
     */
    public function buildAddress(Container $container, ControlGroup $group = null) {
        $container->setCurrentGroup($group);

        $container->addText('first_row', 'První řádek')
                ->setOption('description', 'První volitelný řádek adresy (např. bytem u)');

        $container->addText('second_row', 'Druhý řádek')
                ->setOption('description', 'Druhý volitelný řádek adresy (použití zřídka)');


        $container->addText('target', 'Místo')
                ->addRule(Form::FILLED, 'Adresa musí mít vyplněné místo.')
                ->setOption('description', 'Nejčastěji ulice a číslo, ale třeba i P. O. Box.');

        $container->addText('city', 'Město')
                ->addRule(Form::FILLED, 'Adresa musí mít vyplněné město.');


        $postalCode = $container->addText('postal_code', 'PSČ')
                ->addRule(Form::MAX_LENGTH, null, 5)
                ->setOption('description', 'Bez mezer');



        $country = $container->addSelect('country_iso', 'Stát');
        $country->setItems($this->serviceCountry->getTable()->order('name_cs')->fetchPairs('country_iso', 'name_cs')); //TODO i18n
        $country->setPrompt('(Stát dle PSČ)');

        /* Country + postal code validation */
        $addressService = $this->serviceAddress;
        $regionService = $this->serviceRegion;
        $validPostalCode = function(BaseControl $control) use($addressService) {
                    return $addressService->tryInferRegion($control->getValue());
                };
        $postalCode->addConditionOn($country, function(BaseControl $control) {
                    $value = $control->getValue();
                    return in_array($value, array('CZ', 'SK'));
                })->addRule(Form::FILLED, 'Adresa musí mít vyplněné PSČ.');
        $postalCode->addCondition(Form::FILLED)
                ->addRule($validPostalCode, 'Neplatný formát PSČ.');

        $country->addConditionOn($postalCode, function(BaseControl $control) use($addressService) {
                    return !$addressService->tryInferRegion($control->getValue());
                })->addRule(Form::FILLED, 'Stát musí být vyplněn.');
        $country->addCondition(Form::FILLED)
                ->addConditionOn($postalCode, $validPostalCode)->addRule(function (BaseControl $control) use($regionService, $addressService, $postalCode) {
                    $regionId = $addressService->inferRegion($postalCode->getValue());
                    $region = $regionService->findByPrimary($regionId);
                    return $region->country_iso == $control->getValue();
                }, 'Zvolený stát neodpovídá zadanému PSČ.');

        //$container->addHidden('address_id');
    }

    public function createTypeElement() {
        $element = new RadioList('Typ adresy');
        $element->setItems(array(
            'P' => 'trvalá',
            'D' => 'doručovací (odlišná od trvalé)'
        ));
        $element->setDefaultValue('P');
        return $element;
    }

}
