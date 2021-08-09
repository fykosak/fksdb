<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\AddressContainer;
use FKSDB\Components\Forms\Controls\WriteOnly\WriteOnlyInput;
use FKSDB\Models\ORM\Services\ServiceAddress;
use FKSDB\Models\ORM\Services\ServiceRegion;
use FKSDB\Models\Persons\ReferencedPersonHandler;
use Nette\DI\Container;
use Nette\Forms\Control;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form as BaseForm;

class AddressFactory
{

    private ServiceAddress $serviceAddress;

    private ServiceRegion $serviceRegion;

    private Container $container;

    public function __construct(Container $container, ServiceAddress $serviceAddress, ServiceRegion $serviceRegion)
    {
        $this->serviceAddress = $serviceAddress;
        $this->serviceRegion = $serviceRegion;
        $this->container = $container;
    }

    public function createAddress(
        ?Control $conditioningField = null,
        bool $required = false,
        bool $notWriteOnly = false,
        bool $showExtendedRows = false
    ): AddressContainer {
        $container = new AddressContainer($this->container);
        $this->buildAddress2($container, $conditioningField, $required, $notWriteOnly, $showExtendedRows);
        return $container;
    }

    public function createAddressContainer(string $type): AddressContainer
    {
        $container = new AddressContainer($this->container);
        $this->buildAddress2($container, null, false, true); // TODO is not safe
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

    public function buildAddress2(
        AddressContainer $container,
        ?Control $conditioningField = null,
        bool $required = false,
        bool $notWriteOnly = false,
        bool $showExtendedRows = false
    ): void {
        if ($showExtendedRows) {
            $container->addText('first_row', _('First row'))
                ->setOption('description', _('First optional row of the address (e.g. title)'));

            $container->addText('second_row', _('Second row'))
                ->setOption('description', _('Second optional row of the address (used rarely)'));
        }

        $target = new WriteOnlyInput(_('Place'));
        $container->addComponent($target, 'target');
        $target->setOption('description', _('Typically street and (house) number.'));
        if ($required) {
            $conditioned = $conditioningField ? $target->addConditionOn($conditioningField, BaseForm::FILLED) : $target;
            $conditioned->addRule(BaseForm::FILLED, _('The place is required.'));
        }
        if ($notWriteOnly) {
            $target->setWriteOnly(false);
        }

        $city = new WriteOnlyInput(_('City'));
        $container->addComponent($city, 'city');
        if ($required) {
            $conditioned = $conditioningField ? $city->addConditionOn($conditioningField, BaseForm::FILLED) : $city;
            $conditioned->addRule(BaseForm::FILLED, _('City is required.'));
        }
        if ($notWriteOnly) {
            $city->setWriteOnly(false);
        }

        $postalCode = $container->addText('postal_code', _('postal code'))
            ->addRule(BaseForm::MAX_LENGTH, null, 5)
            ->setOption('description', _('Without spaces. For the Czech Republic or Slovakia only.'));

        $country = $container->addSelect('country_iso', _('Country'));
        $country->setItems($this->serviceRegion->getCountries()->order('name')->fetchPairs('country_iso', 'name'));
        $country->setPrompt(_('Detect country from postal code (CR, SK only)'));

        // check valid address structure
        $target->addConditionOn($city, BaseForm::FILLED)->addRule(
            BaseForm::FILLED,
            _('You have to fill in the place when the city is filled.')
        );
        $target->addConditionOn($postalCode, BaseForm::FILLED)->addRule(
            BaseForm::FILLED,
            _('You have to fill in the place when the postal code is filled.')
        );
        $target->addConditionOn($country, BaseForm::FILLED)->addRule(
            BaseForm::FILLED,
            _('You have to fill in the place when the country is filled.')
        );

        /* Country + postal code validation */
        $validPostalCode = fn(BaseControl $control): bool => $this->serviceAddress->tryInferRegion(
            $control->getValue()
        );

        if ($required) {
            $conditioned = $conditioningField ? $postalCode->addConditionOn(
                $conditioningField,
                BaseForm::FILLED
            ) : $postalCode;
            $conditioned->addConditionOn($country, function (BaseControl $control): bool {
                $value = $control->getValue();
                return in_array($value, ['CZ', 'SK']);
            })->addRule(BaseForm::FILLED, _('Postal code is required.'));
        }
        $postalCode->addCondition(BaseForm::FILLED)
            ->addRule($validPostalCode, _('Invalid postal code.'));

        if ($required) {
            $conditioned = $conditioningField ? $country->addConditionOn(
                $conditioningField,
                BaseForm::FILLED
            ) : $country;
            $conditioned->addConditionOn(
                $postalCode,
                fn(BaseControl $control): bool => !$this->serviceAddress->tryInferRegion($control->getValue())
            )->addRule(BaseForm::FILLED, _('Country is required.'));
        }
        $country->addCondition(BaseForm::FILLED)
            ->addConditionOn($postalCode, $validPostalCode)->addRule(
                function (BaseControl $control) use ($postalCode): bool {
                    $regionId = $this->serviceAddress->inferRegion($postalCode->getValue());
                    $region = $this->serviceRegion->findByPrimary($regionId);
                    return $region->country_iso == $control->getValue();
                },
                _('Chosen country does not match provided postal code.')
            );
    }
}
