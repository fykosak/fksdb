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

    private iterable $conflicts;

    private ReferencedId $referencedId;

    /**
     * ModelDataConflictException constructor.
     * @param iterable $conflicts
     * @param \Throwable|null $previous
     */
    public function __construct(iterable $conflicts, $previous = null) {
        parent::__construct(null, Response::S409_CONFLICT, $previous);
        $this->conflicts = $conflicts;
    }

    public function getConflicts(): iterable {
        return $this->conflicts;
    }

    public function getReferencedId(): ReferencedId {
        return $this->referencedId;
    }

    public function setReferencedId(ReferencedId $referencedId): void {
        $this->referencedId = $referencedId;
    }
}
