<?php

use Nette\Security\IResource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property string name_abbrev
 * @property integer school_id
 */
class ModelSchool extends AbstractModelSingle implements IResource {
    const REGION = 'region';
    const LABEL = 'label';
    const VALUE = 'value';

    /**
     * @return ModelAddress
     */
    public function getAddress() {
        $data = $this->address;
        return ModelAddress::createFromTableRow($data);
    }

    public function getResourceId() {
        return 'school';
    }

    public function __toArray() {
        return [
            self::LABEL => $this->name_abbrev,
            self::VALUE => $this->school_id,
            self::REGION => $this->getAddress()->region->country_iso,
        ];
    }

}
