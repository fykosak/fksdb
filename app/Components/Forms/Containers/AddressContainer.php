<?php

namespace FKSDB\Components\Forms\Containers;

use FKSDB\ORM\AbstractModelMulti;
use FKSDB\ORM\Models\ModelAddress;
use FKSDB\ORM\Models\ModelRegion;
use FKSDB\ORM\Services\ServiceRegion;
use Nette\Database\Table\ActiveRow;
use Nette\Forms\Container;
use Nette\InvalidStateException;
use Nette\Utils\ArrayHash;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class AddressContainer extends ModelContainer {

    /**
     * @var ServiceRegion
     */
    private $serviceRegion;

    /**
     * @param ServiceRegion $serviceRegion
     */
    public function setServiceRegion(ServiceRegion $serviceRegion) {
        $this->serviceRegion = $serviceRegion;
    }

    /**
     * Used for substituing form's IControl (via duck-typing).
     *
     * @param \Traversable $value
     */
    public function setValue($value) {
        $this->setValues($value === null ? [] : $value);
    }

    /**
     * Used for substituing form's IControl (via duck-typing).
     *
     * @param \Traversable $value
     */
    public function setDefaultValue($value) {
        $this->setDefaults($value === null ? [] : $value);
    }

    /**
     * @param $values
     * @param bool $erase
     * @return Container|void
     */
    public function setValues($values, $erase = FALSE) {
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

        parent::setValues($values, $erase);
    }

    /**
     * @param bool $asArray
     * @return array|\Nette\Utils\ArrayHash
     * @return array|ArrayHash
     */
    public function getValues($asArray = FALSE) {
        $values = parent::getValues($asArray);
        if (count($values) && !isset($values['region_id'])) {
            if (!$this->serviceRegion) {
                throw new InvalidStateException("You must set FKSDB\ORM\Services\ServiceRegion before getting values from the address container.");
            }
            /** @var ModelRegion|false $region */
            $region = $this->serviceRegion->getCountries()->where('country_iso', $values['country_iso'])->fetch();
            $values['region_id'] = $region ? $region->region_id : null;
        }
        return $values;
    }
}
