<?php

namespace Events\Model\Holder\SecondaryModelStrategies;

use Events\Model\Holder\BaseHolder;

/**
 * Class SecondaryModelDataConflictException
 * @package Events\Model\Holder\SecondaryModelStrategies
 */
class SecondaryModelDataConflictException extends SecondaryModelConflictException {

    private $conflictData;

    /**
     * SecondaryModelDataConflictException constructor.
     * @param $conflictData
     * @param BaseHolder $baseHolder
     * @param $conflicts
     * @param null $code
     * @param null $previous
     */
    function __construct($conflictData, BaseHolder $baseHolder, $conflicts, $code = null, $previous = null) {
        parent::__construct($baseHolder, $conflicts, $code, $previous);
        $this->conflictData = $conflictData;
        $this->message .= sprintf(' (%s)', implode(', ', $this->conflictData));
    }

    /**
     * @return mixed
     */
    public function getConflictData() {
        return $this->getConflictData();
    }

}
