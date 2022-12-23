<?php

declare(strict_types=1);

namespace NiftyGrid\Components;

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

    public function __construct(string $label)
    {
        $this->label = $label;
    }

    public function setTruncate(?int $truncate): self
    {
        $this->truncate = $truncate;
        return $this;
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

        if (isset($this->truncate)) {
            return Strings::truncate($value ?? '', $this->truncate);
        } else {
            return $value;
        }
    }


    public function setRenderer(callable $renderer): self
    {
        $this->renderer = $renderer;
        return $this;
    }

    public function setSortable(bool $sortable = true): self
    {
        $this->sortable = $sortable;
        return $this;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }
}
