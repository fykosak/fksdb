<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components;

use Nette;
use Nette\Application\UI\Component;
use Nette\Utils\Strings;
use NiftyGrid;

/**
 * NiftyGrid - DataGrid for Nette
 *
 * @author    Jakub Holub
 * @copyright    Copyright (c) 2012 Jakub Holub
 * @license     New BSD Licence
 * @link        http://addons.nette.org/cs/niftygrid
 */
class Column extends Component
{
    public string $label;
    public ?int $truncate = null;
    public bool $sortable = false;
    /** @var callable */
    private $renderer;

    public function __construct(string $label, callable $renderer)
    {
        $this->label = $label;
        $this->renderer = $renderer;
    }

    /**
     * @param mixed $row
     * @return string|Nette\Utils\Html
     */
    public function prepareValue($row)
    {
        if (isset($this->renderer)) {
            $value = ($this->renderer)((object)$row);
        } else {
            $value = $row[$this->getName()];
        }
        return $value;
    }

    public function setSortable(bool $sortable = false): self
    {
        $this->sortable = $sortable;
        return $this;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }
}
