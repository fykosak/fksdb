<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\AddressContainer;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Forms\ControlGroup;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\RadioList;
use ServiceAddress;
use ServiceRegion;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class AddressFactory {

    /**
     * @var ServiceAddress
     */
    private $serviceAddress;

    /**
     * @var ServiceRegion
     */
    private $serviceRegion;

    function __construct(ServiceAddress $serviceAddress, ServiceRegion $serviceRegion) {
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
    public function buildAddress(AddressContainer $container, ControlGroup $group = null) {
        $container->setServiceRegion($this->serviceRegion);
        $container->setCurrentGroup($group);

        $container->addText('first_row', _('První řádek'))
                ->setOption('description', _('První volitelný řádek adresy (např. bytem u)'));

        $container->addText('second_row', _('Druhý řádek'))
                ->setOption('description', _('Druhý volitelný řádek adresy (použití zřídka)'));


        $container->addText('target', _('Místo'))
                ->addRule(Form::FILLED, _('Adresa musí mít vyplněné místo.'))
                ->setOption('description', _('Nejčastěji ulice a číslo, ale třeba i P. O. Box.'));

        $container->addText('city', _('Město'))
                ->addRule(Form::FILLED, _('Adresa musí mít vyplněné město.'));


        $postalCode = $container->addText('postal_code', _('PSČ'))
                ->addRule(Form::MAX_LENGTH, null, 5)
                ->setOption('description', _('Bez mezer. Pro Českou republiku nebo Slovensko.'));



        $country = $container->addSelect('country_iso', _('Stát'));
        $country->setItems($this->serviceRegion->getCountries()->order('name')->fetchPairs('country_iso', 'name'));
        $country->setPrompt(_('Určit stát dle PSČ'));

        /* Country + postal code validation */
        $addressService = $this->serviceAddress;
        $regionService = $this->serviceRegion;
        $validPostalCode = function(BaseControl $control) use($addressService) {
                    return $addressService->tryInferRegion($control->getValue());
                };
        $postalCode->addConditionOn($country, function(BaseControl $control) {
                    $value = $control->getValue();
                    return in_array($value, array('CZ', 'SK'));
                })->addRule(Form::FILLED, _('Adresa musí mít vyplněné PSČ.'));
        $postalCode->addCondition(Form::FILLED)
                ->addRule($validPostalCode, _('Neplatný formát PSČ.'));

        $country->addConditionOn($postalCode, function(BaseControl $control) use($addressService) {
                    return !$addressService->tryInferRegion($control->getValue());
                })->addRule(Form::FILLED, _('Stát musí být vyplněn.'));
        $country->addCondition(Form::FILLED)
                ->addConditionOn($postalCode, $validPostalCode)->addRule(function (BaseControl $control) use($regionService, $addressService, $postalCode) {
                    $regionId = $addressService->inferRegion($postalCode->getValue());
                    $region = $regionService->findByPrimary($regionId);
                    return $region->country_iso == $control->getValue();
                }, _('Zvolený stát neodpovídá zadanému PSČ.'));

        //$container->addHidden('address_id');
    }

    public function createTypeElement() {
        $element = new RadioList(_('Typ adresy'));
        $element->setItems(array(
            'P' => _('trvalá'),
            'D' => _('doručovací (odlišná od trvalé)')
        ));
        $element->setDefaultValue('P');
        return $element;
    }

}
