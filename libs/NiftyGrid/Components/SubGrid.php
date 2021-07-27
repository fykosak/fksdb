<?php
/**
 * NiftyGrid - DataGrid for Nette
 *
 * @author    Jakub Holub
 * @copyright    Copyright (c) 2012 Jakub Holub
 * @license     New BSD Licence
 * @link        http://addons.nette.org/cs/niftygrid
 */

namespace NiftyGrid\Components;

use Nette;
use Nette\Application\UI\Component;
use Nette\Utils\Html;
use NiftyGrid,
    NiftyGrid\Grid;

class SubGrid extends Component {

    /** @var string */
    public $name;

    /** @var callback|string */
    public $label;

    /** @var callback|string */
    private $link;

    /** @var callback */
    private $settings;

    public bool $ajax = true;

    /** @var callback|string */
    public $class;

    /** @var callback|string */
    public $cellStyle;

    /** @var callback|string */
    public $show = true;

    /**
     * @param string $name
     * @return SubGrid
     */
    public function setName($name): self {
        $this->name = $name;

        return $this;
    }

    /**
     * @param callback|string $label
     * @return SubGrid
     */
    public function setLabel($label): self {
        $this->label = $label;

        return $this;
    }

    /**
     * @param array $row
     * @return mixed|string
     */
    public function getLabel($row) {
        if (is_callable($this->label)) {
            return call_user_func($this->label, $row);
        }
        return $this->label;
    }

    /**
     * @param callback|string $class
     * @return SubGrid
     */
    public function setClass($class): self {
        $this->class = $class;

        return $this;
    }

    /**
     * @param $row
     * @return string
     */
    public function getClass($row) {
        if (is_callable($this->class)) {
            return call_user_func($this->class, $row);
        }
        return $this->class;
    }

    /**
     * @param callback|string $link
     * @return SubGrid
     */
    public function setLink($link): self {
        $this->link = $link;

        return $this;
    }

    /**
     * @param array $row
     * @return mixed
     */
    public function getLink($row) {
        return call_user_func($this->link, $row);
    }

    /**
     * @param callback|string $cellStyle
     * @return SubGrid
     */
    public function setCellStyle($cellStyle): self {
        $this->cellStyle = $cellStyle;

        return $this;
    }

    public function hasCellStyle(): bool {
        return isset($this->cellStyle);
    }

    /**
     * @return string
     */
    public function getCellStyle() {
        if (is_callable($this->cellStyle)) {
            return call_user_func($this->cellStyle, $this->grid);
        }
        return $this->cellStyle;
    }

    /**
     * @param callback|string $show
     * @return static
     */
    public function setShow($show): self {
        $this->show = $show;

        return $this;
    }

    /**
     * @param array $row
     * @return callback|mixed|string
     */
    public function getShow($row) {
        if (is_callable($this->show)) {
            return (boolean)call_user_func($this->show, $row);
        }
        return $this->show;
    }

    /**
     * @param Grid $grid
     * @return SubGrid
     */
    public function setGrid(Grid $grid): self {
        $this->grid = $grid;

        return $this;
    }

    public function setAjax(bool $ajax = true): self {
        $this->ajax = $ajax;

        return $this;
    }

    /**
     * @param callback $settings
     * @return SubGrid
     */
    public function settings($settings): self {
        $this->settings = $settings;

        return $this;
    }

    /**
     * @return Grid
     */
    public function getGrid() {
        $this->grid->isSubGrid = true;
        $this->grid->afterConfigureSettings = $this->settings;
        return $this->grid;
    }

    /**
     * @param array $row
     */
    public function render($row): void {
        if (!$this->getShow($row)) {
            return;
        }

        $el = Html::el("a")
            ->href($this->getLink($row))
            ->addClass($this->getClass($row))
            ->addClass("grid-button")
            ->setTitle($this->getLabel($row));

        if ($this->ajax) {
            $el->addClass("grid-ajax");
        }
        echo $el;
    }
}
