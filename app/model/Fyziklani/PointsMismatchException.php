<?php


namespace FKSDB\Fyziklani;


use Throwable;

/**
 * Class PointsMismatchException
 * *
 */
class PointsMismatchException extends TaskCodeException {
    /**
     * PointsMismatchException constructor.
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(int $code = 0, Throwable $previous = null) {
        parent::__construct(_('Points mismatch'), $code, $previous);
    }
}
