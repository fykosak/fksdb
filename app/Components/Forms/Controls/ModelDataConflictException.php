<?php

namespace FKSDB\Components\Forms\Controls;

use Nette\ArrayHash;
use RuntimeException;

class ModelDataConflictException extends RuntimeException {

    /** @var ArrayHash */
    private $conflicts;

    /** @var ReferencedId */
    private $referencedId;

    public function __construct($conflicts, $code = null, $previous = null) {
        parent::__construct(null, $code, $previous);
        $this->conflicts = $conflicts;
    }

    public function getConflicts() {
        return $this->conflicts;
    }

    public function getReferencedId() {
        return $this->referencedId;
    }

    public function setReferencedId(ReferencedId $referencedId) {
        $this->referencedId = $referencedId;
    }

}
