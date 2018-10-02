<?php

namespace FKSDB\Components\Forms\Containers;

use AbstractModelMulti;
use Nette\Database\Table\ActiveRow;
use Nette\InvalidStateException;
use ServiceRegion;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class AddressContainer extends ModelContainer {

    /**
     * @var ServiceRegion
     */
    private $serviceRegion;

    public function setServiceRegion(ServiceRegion $serviceRegion) {
        $this->serviceRegion = $serviceRegion;
    }

    /**
     * Used for substituing form's IControl (via duck-typing).
     *
     * @param Traversable $value
     */
    public function setValue($value) {
        $this->setValues($value === null ? [] : $value);
    }

    /**
     * Used for substituing form's IControl (via duck-typing).
     *
     * @param Traversable $value
     */
    public function setDefaultValue($value) {
        $this->setDefaults($value === null ? [] : $value);
    }

    public function setValues($values, $erase = FALSE) {
        if ($values instanceof ActiveRow || $values instanceof AbstractModelMulti) { //assert its from address table
            if ($values instanceof AbstractModelMulti) {
                $address = $values->getMainModel();
            } else {
                $address = $values;
            }

            $values = $address->toArray();
            $values['country_iso'] = $address->region_id ? $address->region->country_iso : null;
        } else if (is_array($values) && isset($values['region_id'])) {
            $region = $this->serviceRegion->findByPrimary($values['region_id']);
            $values['country_iso'] = $region->country_iso;
        }

        parent::setValues($values, $erase);
    }

    public function getValues($asArray = FALSE) {
        $values = parent::getValues($asArray);
        if (count($values) && !isset($values['region_id'])) {
            if (!$this->serviceRegion) {
                throw new InvalidStateException("You must set ServiceRegion before getting values from the address container.");
            }
            $region = $this->serviceRegion->getCountries()->where('country_iso', $values['country_iso'])->fetch();
            $values['region_id'] = $region ? $region->region_id : null;
        }

        return $values;
    }

}
