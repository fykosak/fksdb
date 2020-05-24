<?php

namespace FKSDB\Components\Forms\Controls;

use Nette\Http\Response;
use Nette\Utils\ArrayHash;
use RuntimeException;

/**
 * Class ModelDataConflictException
 * *
 */
class ModelDataConflictException extends RuntimeException {

    /** @var ArrayHash */
    private $conflicts;

    /** @var ReferencedId */
    private $referencedId;

    /**
     * ModelDataConflictException constructor.
     * @param $conflicts
     * @param null $previous
     */
    public function __construct($conflicts, $previous = null) {
        parent::__construct(null, Response::S409_CONFLICT, $previous);
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
