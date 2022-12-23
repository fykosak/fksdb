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

    public function __construct(string $label, string $link)
    {
        $this->link = $link;
        $this->label = $label;
    }

    public function setClass(string $class): self
    {
        $this->class = $class;
        return $this;
    }

    public function toHtml(): Html
    {
        return Html::el('a')
            ->href($this->link)
            ->addAttributes([
                'class' => 'grid-global-button grid-button ' . ($this->class ?? ''),
                'title' => $this->label,
            ])
            ->addText($this->label);
    }

    public function render(): void
    {
        echo $this->toHtml();
    }
}
