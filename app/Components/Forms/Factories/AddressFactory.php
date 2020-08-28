<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\AddressContainer;
use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use FKSDB\ORM\Services\ServiceAddress;
use FKSDB\ORM\Services\ServiceRegion;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\IControl;
use FKSDB\Persons\ReferencedPersonHandler;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class AddressFactory {

    public const SHOW_EXTENDED_ROWS = 0x1;
    public const REQUIRED = 0x2;
    public const NOT_WRITEONLY = 0x4;

    private ServiceAddress $serviceAddress;

    private ServiceRegion $serviceRegion;

    private Container $container;

    /**
     * AddressFactory constructor.
     * @param Container $container
     * @param ServiceAddress $serviceAddress
     * @param ServiceRegion $serviceRegion
     */
    public function __construct(Container $container, ServiceAddress $serviceAddress, ServiceRegion $serviceRegion) {
        $this->serviceAddress = $serviceAddress;
        $this->serviceRegion = $serviceRegion;
        $this->container = $container;
    }

    public function createAddress(int $options = 0, ?IControl $conditioningField = null): AddressContainer {
        $container = new AddressContainer($this->container);
        $this->buildAddress($container, $options, $conditioningField);
        return $container;
    }

    public function createAddressContainer(string $type): AddressContainer {
        $container = new AddressContainer($this->container);
        $this->buildAddress($container, self::NOT_WRITEONLY); // TODO is not safe
        switch ($type) {
            case ReferencedPersonHandler::POST_CONTACT_DELIVERY:
                $container->setOption('label', _('Delivery address'));
                break;
            case ReferencedPersonHandler::POST_CONTACT_PERMANENT:
                $container->setOption('label', _('Permanent address') . _('(when different from delivery address)'));
                break;
        }
        return $container;
    }

    /**
     * Appends elements to an existing container.
     * (Created because of KdybyReplicator.)
     *
     * @param AddressContainer $container
     * @param IControl $conditioningField
     * @param int $options
     */
    public function buildAddress(AddressContainer $container, int $options = 0, ?IControl $conditioningField = null): void {
        if ($options & self::SHOW_EXTENDED_ROWS) {
            $container->addText('first_row', _('První řádek'))
                ->setOption('description', _('První volitelný řádek adresy (např. bytem u)'));

            $container->addText('second_row', _('Druhý řádek'))
                ->setOption('description', _('Druhý volitelný řádek adresy (použití zřídka)'));
        }

        $target = new WriteOnlyInput(_('Místo'));
        $container->addComponent($target, 'target');
        $target->setOption('description', _('Typicky ulice a číslo popisné.'));
        if ($options & self::REQUIRED) {
            $conditioned = $conditioningField ? $target->addConditionOn($conditioningField, Form::FILLED) : $target;
            $conditioned->addRule(Form::FILLED, _('Adresa musí mít vyplněné místo.'));
        }
        if ($options & self::NOT_WRITEONLY) {
            $target->setWriteOnly(false);
        }

        $city = new WriteOnlyInput(_('Město'));
        $container->addComponent($city, 'city');
        if ($options & self::REQUIRED) {
            $conditioned = $conditioningField ? $city->addConditionOn($conditioningField, Form::FILLED) : $city;
            $conditioned->addRule(Form::FILLED, _('Adresa musí mít vyplněné město.'));
        }
        if ($options & self::NOT_WRITEONLY) {
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
        $validPostalCode = function (BaseControl $control): bool {
            return $this->serviceAddress->tryInferRegion($control->getValue());
        };

        if ($options & self::REQUIRED) {
            $conditioned = $conditioningField ? $postalCode->addConditionOn($conditioningField, Form::FILLED) : $postalCode;
            $conditioned->addConditionOn($country, function (BaseControl $control) {
                $value = $control->getValue();
                return in_array($value, ['CZ', 'SK']);
            })->addRule(Form::FILLED, _('Adresa musí mít vyplněné PSČ.'));
        }
        $postalCode->addCondition(Form::FILLED)
            ->addRule($validPostalCode, _('Neplatný formát PSČ.'));

        if ($options & self::REQUIRED) {
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
