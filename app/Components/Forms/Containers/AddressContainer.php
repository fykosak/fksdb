<?php

namespace FKSDB\Components\Forms\Containers;

use FKSDB\Models\ORM\Models\ModelAddress;
use FKSDB\Models\ORM\Models\ModelPostContact;
use FKSDB\Models\ORM\Models\ModelRegion;
use FKSDB\Models\ORM\Services\ServiceRegion;
use Nette\Database\Table\ActiveRow;
use Nette\DI\Container as DIContainer;
use Nette\InvalidStateException;
use Nette\Utils\ArrayHash;

class AddressContainer extends ModelContainer
{

    private ServiceRegion $serviceRegion;

    public function __construct(DIContainer $container)
    {
        parent::__construct($container);
    }

    final public function injectServiceRegion(ServiceRegion $serviceRegion): void
    {
        $this->serviceRegion = $serviceRegion;
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
        $this->setDefaults($value === null ? [] : $value);
    }

    /**
     * @param ModelPostContact|mixed $data
     * @param bool $erase
     * @return static
     */
    public function setValues($data, bool $erase = false): self
    {
        if ($data instanceof ActiveRow) { //assert its from address table
            if ($data instanceof ModelPostContact) {
                $address = $data->getAddress();
            } else {
                $address = $data;
            }
            /** @var ModelAddress $address */
            $data = $address->toArray();
            $data['country_iso'] = $address->region_id ? $address->getRegion()->country_iso : null;
        } elseif (is_array($data) && isset($data['region_id'])) {
            $region = $this->serviceRegion->findByPrimary($data['region_id']);
            $data['country_iso'] = $region->country_iso;
        }

        return parent::setValues($data, $erase);
    }

    /**
     * @param null $returnType
     * @param array|null $controls
     * @return array|ArrayHash
     */
    public function getUnsafeValues($returnType = null, array $controls = null)
    {
        $values = parent::getUnsafeValues($returnType);
        if (count($values) && !isset($values['region_id'])) {
            if (!$this->serviceRegion) {
                throw new InvalidStateException('You must set FKSDB\Models\ORM\Services\ServiceRegion before getting values from the address container.');
            }
            /** @var ModelRegion|null $region */
            $region = $this->serviceRegion->getCountries()->where('country_iso', $values['country_iso'])->fetch();
            $values['region_id'] = $region ? $region->region_id : null;
        }
        return $values;
    }
}
