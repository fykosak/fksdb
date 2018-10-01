<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\AddressContainer;
use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use FKSDB\Components\Forms\Factories\Address\CityField;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\IControl;
use ServiceAddress;
use ServiceRegion;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class AddressFactory {

    const SHOW_EXTENDED_ROWS = 0x1;
    const REQUIRED = 0x2;
    const NOT_WRITEONLY = 0x4;

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

    public function createAddress(IControl $conditioningField = null, $required = false, $notWriteOnly = false, $showExtendedRows = false) {
        $container = new AddressContainer();
        $this->buildAddress($container, $conditioningField, $required, $notWriteOnly, $showExtendedRows);
        return $container;
    }

    /**
     * Appends elements to an existing container.
     *
     * @param AddressContainer $container
     * @param IControl $conditioningField
     * @param boolean $notWriteOnly
     * @param boolean $required
     * @param boolean $showExtendedRows
     */
    private function buildAddress(
        AddressContainer $container,
        IControl $conditioningField = null,
        $required = false,
        $notWriteOnly = false,
        $showExtendedRows = false
    ) {
        $container->setServiceRegion($this->serviceRegion);


        if ($showExtendedRows) {
            $container->addText('first_row', _('První řádek'))
                ->setOption('description', _('První volitelný řádek adresy (např. bytem u)'));

            $container->addText('second_row', _('Druhý řádek'))
                ->setOption('description', _('Druhý volitelný řádek adresy (použití zřídka)'));
        }

        $target = new WriteOnlyInput(_('Místo'));
        $container->addComponent($target, 'target');
        $target->setOption('description', _('Typicky ulice a číslo popisné.'));
        if ($required) {
            $conditioned = $conditioningField ? $target->addConditionOn($conditioningField, Form::FILLED) : $target;
            $conditioned->addRule(Form::FILLED, _('Adresa musí mít vyplněné místo.'));
        }
        if ($notWriteOnly) {
            $target->setWriteOnly(false);
        }

        $city = new CityField();
        $container->addComponent($city, 'city');
        if ($required) {
            $conditioned = $conditioningField ? $city->addConditionOn($conditioningField, Form::FILLED) : $city;
            $conditioned->addRule(Form::FILLED, _('Adresa musí mít vyplněné město.'));
        }
        if ($notWriteOnly) {
            $city->setWriteOnly(false);
        }


        $postalCode = $container->addText('postal_code', _('PSČ'))
            ->addRule(Form::MAX_LENGTH, null, 5)
            ->setOption('description', _('Bez mezer. Pro Českou republiku nebo Slovensko.'));


        $country = $container->addSelect('country_iso', _('Stát'));
        $country->setItems($this->serviceRegion->getCountries()->order('name')->fetchPairs('country_iso', 'name'));
        $country->setPrompt(_('Určit stát dle PSČ'));

        // check valid address structure
        $target->addConditionOn($city, Form::FILLED)->addRule(Form::FILLED, _('Při vyplněném městě musí mít adresa vyplněno i místo.'));
        $target->addConditionOn($postalCode, Form::FILLED)->addRule(Form::FILLED, _('Při vyplněném PSČ musí mít adresa vyplněno i místo.'));
        $target->addConditionOn($country, Form::FILLED)->addRule(Form::FILLED, _('Při vyplněném státu musí mít adresa vyplněno i místo.'));

        /* Country + postal code validation */
        $validPostalCode = function (BaseControl $control) {
            return $this->serviceAddress->tryInferRegion($control->getValue());
        };

        if ($required) {
            $conditioned = $conditioningField ? $postalCode->addConditionOn($conditioningField, Form::FILLED) : $postalCode;
            $conditioned->addConditionOn($country, function (BaseControl $control) {
                $value = $control->getValue();
                return in_array($value, ['CZ', 'SK']);
            })->addRule(Form::FILLED, _('Adresa musí mít vyplněné PSČ.'));
        }
        $postalCode->addCondition(Form::FILLED)
            ->addRule($validPostalCode, _('Neplatný formát PSČ.'));

        if ($required) {
            $conditioned = $conditioningField ? $country->addConditionOn($conditioningField, Form::FILLED) : $country;
            $conditioned->addConditionOn($postalCode, function (BaseControl $control) {
                return !$this->serviceAddress->tryInferRegion($control->getValue());
            })->addRule(Form::FILLED, _('Stát musí být vyplněn.'));
        }
        $country->addCondition(Form::FILLED)
            ->addConditionOn($postalCode, $validPostalCode)->addRule(function (BaseControl $control) use ($postalCode) {
                $regionId = $this->serviceAddress->inferRegion($postalCode->getValue());
                $region = $this->serviceRegion->findByPrimary($regionId);
                return $region->country_iso == $control->getValue();
            }, _('Zvolený stát neodpovídá zadanému PSČ.'));
    }

}
