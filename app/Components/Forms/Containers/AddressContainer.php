<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Containers;

use FKSDB\Models\ORM\Models\AddressModel;
use FKSDB\Models\ORM\Models\CountryModel;
use FKSDB\Models\ORM\Models\PostContactModel;
use FKSDB\Models\ORM\Services\CountryService;
use Fykosak\NetteORM\Model;
use Nette\DI\Container as DIContainer;

class AddressContainer extends ModelContainer
{
    private CountryService $countryService;

    public function __construct(DIContainer $container)
    {
        parent::__construct($container);
    }

    final public function injectServiceCountry(CountryService $countryService): void
    {
        $this->countryService = $countryService;
    }

    /**
     * Used for substituting form's IControl (via duck-typing).
     *
     * @param iterable|null $value
     */
    public function setValue($value): void
    {
        $this->setValues($value ?? []);
    }

    /**
     * Used for substituting form's IControl (via duck-typing).
     *
     * @param iterable $value
     */
    public function setDefaultValue($value): void
    {
        $this->setDefaults($value ?? []);
    }

    /**
     * @param PostContactModel|mixed $data
     * @return static
     */
    public function setValues($data, bool $erase = false): self
    {
        if ($data instanceof Model) { //assert its from address table
            if ($data instanceof PostContactModel) {
                $address = $data->address;
            } else {
                $address = $data;
            }
            /** @var AddressModel $address */
            $data = $address->toArray();
            $data['country_iso'] = $address->country ? $address->country->alpha_2 : null;
        } elseif (is_array($data) && isset($data['country_id'])) {
            $country = $this->countryService->findByPrimary($data['country_id']);
            $data['country_iso'] = $country->alpha_2;
        }

        return parent::setValues($data, $erase);
    }

    /**
     * @param null $returnType
     * @return array|object
     */
    public function getUnsafeValues($returnType = null, array $controls = null)
    {
        $values = parent::getUnsafeValues($returnType);
        if (count($values) && !isset($values['country_id'])) {
            /** @var CountryModel|null $country */
            $country = $this->countryService->getTable()->where('alpha_2', $values['country_iso'])->fetch();
            $values['country_id'] = $country ? $country->country_id : null;
        }
        return $values;
    }
}
