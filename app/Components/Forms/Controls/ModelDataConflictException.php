<?php

namespace FKSDB\Components\Forms\Controls;

use Nette\Utils\ArrayHash;
use RuntimeException;

/**
 * Class ModelDataConflictException
 * @package FKSDB\Components\Forms\Controls
 */
class ModelDataConflictException extends RuntimeException {

    /** @var ArrayHash */
    private $conflicts;

    /** @var ReferencedId */
    private $referencedId;

    /**
     * ModelDataConflictException constructor.
     * @param $conflicts
     * @param null $code
     * @param null $previous
     */
    public function __construct($conflicts, $code = null, $previous = null) {
        parent::__construct(null, $code, $previous);
        $this->conflicts = $conflicts;
    }

    /**
     * @return ArrayHash
     */
    public function getConflicts() {
        return $this->conflicts;
    }

    /**
     * @return ReferencedId
     */
    public function getReferencedId() {
        return $this->referencedId;
    }

    /**
     * @param ReferencedId $referencedId
     */
    public function setReferencedId(ReferencedId $referencedId) {
        $this->referencedId = $referencedId;
    }

}
