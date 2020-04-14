<?php

namespace FKSDB\Expressions;

use Nette\Application\BadRequestException;

/**
 * Class BadTypeException
 * @package FKSDB\Expressions
 */
class BadTypeException extends BadRequestException {
    /**
     * BadTypeException constructor.
     * @param string $expected
     * @param $got
     * @param \Exception|NULL $previous
     */
    public function __construct(string $expected, $got, \Exception $previous = NULL) {
        parent::__construct(sprintf(_('Expected presenter of %s type, got %s.'), $expected, get_class($got)), 500, $previous);
    }
}
