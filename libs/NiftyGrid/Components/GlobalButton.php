<?php

declare(strict_types=1);
/**
 * NiftyGrid - DataGrid for Nette
 *
 * @author    Jakub Holub
 * @copyright    Copyright (c) 2012 Jakub Holub
 * @license     New BSD Licence
 * @link        http://addons.nette.org/cs/niftygrid
 */

namespace NiftyGrid\Components;

use Nette\Application\UI\Component;
use Nette\Utils\Html,
    NiftyGrid\Grid;

// For constant only

class GlobalButton extends Component {

    /** @var string */
    private $label;

    /** @var string */
    private $class;

    /** @var callback|string */
    private $link;

    private bool $ajax = true;

    /**
     * @param string $label
     * @return static
     */
    public function setLabel($label): self {
        $this->label = $label;

        return $this;
    }

    /**
     * @param callback|string $class
     * @return static
     */
    public function setClass($class): self {
        $this->class = $class;

        return $this;
    }

    /**
     * @param callback|string $link
     * @return static
     */
    public function setLink($link): self {
        $this->link = $link;

        return $this;
    }

    /**
     * @return string
     */
    private function getLink() {
        if (is_callable($this->link)) {
            return call_user_func($this->link);
        }
        return $this->link;
    }

    public function setAjax(bool $ajax = true): self {
        $this->ajax = $ajax;

        return $this;
    }

    public function render(): void {
        $el = Html::el("a")
            ->href($this->getLink())
            ->setClass($this->class)
            ->addClass("grid-button")
            ->addClass("grid-global-button")
            ->setTitle($this->label)
            ->setText($this->label);

        if ($this->getName() == Grid::ADD_ROW) {
            $el->addClass("grid-add-row");
        }

        if ($this->ajax) {
            $el->addClass("grid-ajax");
        }
        echo $el;
    }
}
