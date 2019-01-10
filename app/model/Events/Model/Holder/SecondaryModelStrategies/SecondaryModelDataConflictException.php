<?php

namespace Events\Model\Holder\SecondaryModelStrategies;

use Events\Model\Holder\BaseHolder;

class SecondaryModelDataConflictException extends SecondaryModelConflictException {

    private $conflictData;

    function __construct($conflictData, BaseHolder $baseHolder, $conflicts, $code = null, $previous = null) {
        parent::__construct($baseHolder, $conflicts, $code, $previous);
        $this->conflictData = $conflictData;
        $this->message .= sprintf(' (%s)', implode(', ', $this->conflictData));
    }

    public function getConflictData() {
        return $this->getConflictData();
    }

}
