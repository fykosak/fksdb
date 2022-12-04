<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent;

use Nette\InvalidStateException;

class ListRow
{
    /** @var callable */
    private $render = null;
    private array $cols = [];

    public string $className = 'row';

    public function addColumn(ListColum $colum): void
    {
        if (isset($this->render)) {
            throw new InvalidStateException('Cannot set cols and renderer at the same cols');
        }
        $this->cols[] = $colum;
    }

    public function setRender(callable $render): void
    {
        if (count($this->cols)) {
            throw new InvalidStateException('Cannot set cols and renderer at the same cols');
        }
        $this->render = $render;
    }
}
