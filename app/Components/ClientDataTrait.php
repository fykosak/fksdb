<?php

namespace FKSDB\Components;

trait ClientDataTrait
{

    private array $clientData = [];

    /**
     * @param string|int $key
     * @param null|array|object|mixed $value
     */
    public function setClientData($key, $value): void
    {
        if ($value === null) {
            unset($this->clientData[$key]);
        } elseif (is_array($value)) {
            $this->clientData[$key] = json_encode($value);
        } elseif (is_object($value)) {
            $this->clientData[$key] = json_encode($value);
        } else {
            $this->clientData[$key] = $value;
        }
    }

    /**
     * @param string|int|null $key
     * @return array|null
     */
    public function getClientData($key = null): ?array
    {
        if ($key === null) {
            return $this->clientData;
        } else {
            return isset($this->clientData[$key]) ? $this->clientData[$key] : null;
        }
    }
}
