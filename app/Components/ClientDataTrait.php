<?php

namespace FKSDB\Components;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
trait ClientDataTrait {

    private $clientData = [];

    public function setClientData($key, $value) {
        if ($value === null) {
            unset($this->clientData[$key]);
        } else if (is_array($value)) {
            $this->clientData[$key] = json_encode($value);
        } else if (is_object($value)) {
            $this->clientData[$key] = json_encode($value);
        } else {
            $this->clientData[$key] = $value;
        }
    }

    public function getClientData($key = null) {
        if ($key === null) {
            return $this->clientData;
        } else {
            return isset($this->clientData[$key]) ? $this->clientData[$key] : null;
        }
    }

}
