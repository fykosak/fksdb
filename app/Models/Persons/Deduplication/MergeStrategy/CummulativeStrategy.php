<?php

namespace FKSDB\Models\Persons\Deduplication\MergeStrategy;

class CummulativeStrategy implements MergeStrategy {

    private ?string $precedence;

    public function __construct(?string $precedence = null) {
        $this->precedence = $precedence;
    }

    /**
     * @param mixed $trunk
     * @param mixed $merged
     * @return mixed
     */
    public function mergeValues($trunk, $merged) {
        if ($merged === null) {
            return $trunk;
        }
        if ($trunk === null) {
            return $merged;
        }
        if ($this->equals($trunk, $merged)) {
            return $trunk;
        }

        if ($this->precedence == 'trunk') {
            return $trunk;
        } elseif ($this->precedence == 'merged') {
            return $merged;
        }

        throw new CannotMergeException();
    }

    /**
     * @param mixed $trunk
     * @param mixed $merged
     */
    private function equals($trunk, $merged): bool {
        if ($trunk instanceof \DateTime && $merged instanceof \DateTime) {
            return $trunk->getTimestamp() == $merged->getTimestamp();
        } else {
            return $trunk == $merged;
        }
    }
}
