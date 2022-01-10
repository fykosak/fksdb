<?php

declare(strict_types=1);

namespace NiftyGrid\Components;

use Nette\Application\UI\Component;
use Nette\Utils\Html,
    NiftyGrid\Grid;

/**
 * NiftyGrid - DataGrid for Nette
 *
 * @author    Jakub Holub
 * @copyright    Copyright (c) 2012 Jakub Holub
 * @license     New BSD Licence
 * @link        http://addons.nette.org/cs/niftygrid
 */
class GlobalButton extends Component
{
    private string $label;
    private string $class;
    private string $link;

    public function __construct(string $label)
    {
        $this->label = $label;
    }

    public function setClass(string $class): self
    {
        $this->class = $class;
        return $this;
    }

    public function setLink(string $link): self
    {
        $this->link = $link;
        return $this;
    }

    public function render(): void
    {
        $el = Html::el('a')
            ->href($this->link)
            ->setClass($this->class)
            ->addClass('grid-button')
            ->addClass('grid-global-button')
            ->setTitle($this->label)
            ->setText($this->label);
        echo $el;
    }
}
