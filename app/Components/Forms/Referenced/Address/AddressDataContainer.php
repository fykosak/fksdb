<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Referenced\Address;

use FKSDB\Components\Forms\Containers\Models\ReferencedContainer;
use FKSDB\Components\Forms\Controls\ReferencedIdMode;
use FKSDB\Components\Forms\Controls\WriteOnly\WriteOnly;
use FKSDB\Components\Forms\Controls\WriteOnly\WriteOnlyInput;
use FKSDB\Models\ORM\Models\AddressModel;
use FKSDB\Models\ORM\Services\CountryService;
use Fykosak\NetteORM\Model\Model;
use Nette\DI\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;

/**
 * @phpstan-extends ReferencedContainer<AddressModel>
 */
class AddressDataContainer extends ReferencedContainer
{
    private bool $writeOnly;
    private bool $required;
    private CountryService $countryService;

    public function __construct(Container $container, bool $writeOnly = true, bool $required = false)
    {
        parent::__construct($container, false);
        $this->writeOnly = $writeOnly;
        $this->required = $required;
    }

    public function inject(CountryService $countryService): void
    {
        $this->countryService = $countryService;
    }

    protected function configure(): void
    {
        $firstRow = new WriteOnlyInput(_('First row'));
        $firstRow->setOption('description', _('First optional row of the address (e.g. title)'));
        $firstRow->setWriteOnly($this->writeOnly);
        $this->addComponent($firstRow, 'first_row');

        $secondRow = new WriteOnlyInput(_('Second row'));
        $secondRow->setOption('description', _('Second optional row of the address (used rarely)'));
        $secondRow->setWriteOnly($this->writeOnly);
        $this->addComponent($secondRow, 'second_row');

        $target = new WriteOnlyInput(_('Place'));
        $target->setOption('description', _('Typically street and (house) number.'));
        $target->setWriteOnly($this->writeOnly);
        if ($this->required) {
            $target->addRule(Form::FILLED, _('The place is required.'));
        }
        $this->addComponent($target, 'target');

        $city = new WriteOnlyInput(_('City'));
        $city->setWriteOnly($this->writeOnly);
        if ($this->required) {
            $city->addRule(Form::FILLED, _('City is required.'));
        }
        $this->addComponent($city, 'city');

        $postalCode = new WriteOnlyInput(_('postal code'));
        $postalCode->addRule(function (BaseControl $control) {
            $value = $control->getValue();
            if ($value === WriteOnly::VALUE_ORIGINAL) {
                return true;
            }
            return preg_match('/^\d{5}$/', $value);
        }, _('Postal code does not match.'));
        $postalCode->setOption('description', _('Without spaces. For the Czech Republic or Slovakia only.'));
        $postalCode->setWriteOnly($this->writeOnly);
        $this->addComponent($postalCode, 'postal_code');


        $country = $this->addSelect('country_id', _('Country'));
        $country->setItems($this->countryService->getTable()->fetchPairs('country_id', 'name'));
        $country->setPrompt(_('Detect country from postal code (CR, SK only)'));
    }

    public function setModel(?Model $model, ReferencedIdMode $mode): void
    {
        if ($model instanceof AddressModel) {
            $data = $model->toArray();
            $this->setValues($data);
        }
    }
}
