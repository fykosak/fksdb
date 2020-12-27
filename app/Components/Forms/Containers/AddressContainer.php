<?php

namespace FKSDB\Components\Forms\Containers;

use FKSDB\Models\ORM\ModelsMulti\AbstractModelMulti;
use FKSDB\Models\ORM\Models\ModelAddress;
use FKSDB\Models\ORM\Models\ModelRegion;
use FKSDB\Models\ORM\Services\ServiceRegion;
use Nette\Database\Table\ActiveRow;
use Nette\DI\Container as DIContainer;
use Nette\InvalidStateException;
use Nette\Utils\ArrayHash;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class AddressContainer extends ModelContainer {

    private ServiceRegion $serviceRegion;

    public function __construct(DIContainer $container) {
        parent::__construct($container);
    }

    final public function injectServiceRegion(ServiceRegion $serviceRegion): void {
        $this->serviceRegion = $serviceRegion;
    }

    /**
     * Used for substituting form's IControl (via duck-typing).
     *
     * @param iterable|null $value
     */
    public function setValue($value): void {
        $this->setValues($value === null ? [] : $value);
    }

    /**
     * Used for substituting form's IControl (via duck-typing).
     *
     * @param iterable $value
     */
    public function setDefaultValue($value): void {
        $this->setDefaults($value === null ? [] : $value);
    }

    /**
     * @param iterable|mixed $values
     * @param bool $erase
     * @return static
     */
    public function setValues($values, $erase = false): self {
        if ($values instanceof ActiveRow || $values instanceof AbstractModelMulti) { //assert its from address table
            if ($values instanceof AbstractModelMulti) {
                $address = $values->getMainModel();
            } else {
                $address = $values;
            }
            /** @var ModelAddress $address */

            $values = $address->toArray();
            $values['country_iso'] = $address->region_id ? $address->region->country_iso : null;
        } elseif (is_array($values) && isset($values['region_id'])) {
            $region = $this->serviceRegion->findByPrimary($values['region_id']);
            $values['country_iso'] = $region->country_iso;
        }

        return parent::setValues($values, $erase);
    }

    /**
     * @param bool $asArray
     * @return array|ArrayHash
     */
    public function getValues($asArray = false) {
        $values = parent::getValues($asArray);
        if (count($values) && !isset($values['region_id'])) {
            if (!$this->serviceRegion) {
                throw new InvalidStateException("You must set FKSDB\Models\ORM\Services\ServiceRegion before getting values from the address container.");
            }
            /** @var ModelRegion|false $region */
            $region = $this->serviceRegion->getCountries()->where('country_iso', $values['country_iso'])->fetch();
            $values['region_id'] = $region ? $region->region_id : null;
        }
        return $values;
    }
}
