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

use Nette;
use Nette\Application\UI\Component;
use NiftyGrid,
    NiftyGrid\Grid,
    NiftyGrid\FilterCondition;

class Column extends Component {

    /** @var string */
    public $name;

    /** @var string */
    public $tableName;

    /** @var callback|string */
    public $label;

    /** @var string */
    public $width;

    /** @var int */
    public $truncate;

    /** @var callback */
    public $renderCallback;

    /** @var callback */
    public $formRenderer;

    /** @var callback|string */
    public $cellRenderer;

    /** @var bool */
    public $sortable = true;

    /** @var string */
    public $filterType;

    /** @var bool */
    public $autocomplete = false;

    /** @var int */
    public $autocompleteResults = 10;

    /** @var bool */
    public $editable = false;

    /** @var Grid */
    public $parent;

    /**
     * @param Grid $parent
     */
    public function injectParent(Grid $parent): void {
        $this->parent = $parent;
    }

    /**
     * @param string $name
     * @return Column
     */
    public function setName($name): self {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $tableName
     * @return Column
     */
    public function setTableName($tableName): self {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * @param callback|string $label
     * @return Column
     */
    public function setLabel($label): self {
        $this->label = $label;

        return $this;
    }

    /**
     * @param string $width
     * @return Column
     */
    public function setWidth($width): self {
        $this->width = $width;

        return $this;
    }

    /**
     * @param int $truncate
     * @return Column
     */
    public function setTruncate($truncate): self {
        $this->truncate = $truncate;

        return $this;
    }

    /**
     * @param array $row
     * @return string
     */
    public function prepareValue($row) {
        if (isset($this->renderer)) {
            $value = ($this->renderer)((object)$row);
        } else {
            $value = $row[$this->name];
        }

        if (isset($this->truncate)) {
            return \Nette\Utils\Strings::truncate($value ?? '', $this->truncate);
        } else {
            return $value;
        }
    }

    private $renderer;

    /**
     * @param callback $renderer
     * @return Column
     */
    public function setRenderer($renderer): self {
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * @param callback $renderer
     * @return Column
     */
    public function setFormRenderer($renderer): self {
        $this->formRenderer = $renderer;

        return $this;
    }

    /**
     * @param callback|string $renderer
     * @return Column
     */
    public function setCellRenderer($renderer): self {
        $this->cellRenderer = $renderer;

        return $this;
    }

    /**
     * @param array $row
     * @return string
     */
    public function getCellRenderer($row) {
        if (is_callable($this->cellRenderer)) {
            return call_user_func($this->cellRenderer, $row);
        }
        return $this->cellRenderer;
    }

    public function hasCellRenderer(): bool {
        return isset($this->cellRenderer);
    }

    /**
     * @param int $numOfResults
     * @return Column
     * @throws NiftyGrid\InvalidFilterException
     * @throws NiftyGrid\UnknownFilterException
     */
    public function setAutocomplete($numOfResults = 10): self {
        if (!isset($this->filterType)) {
            throw new NiftyGrid\UnknownFilterException("Autocomplete can't be used without filter.");
        } elseif ($this->filterType != FilterCondition::TEXT) {
            throw new NiftyGrid\InvalidFilterException("Autocomplete can be used only with Text filter.");
        }
        $this->parent['gridForm'][$this->parent->name]['filter'][$this->name]->getControlPrototype()
            ->addClass("grid-autocomplete")
            ->addData("column", $this->name)
            ->addData("gridName", $this->parent->getGridPath())
            ->addData("link", $this->parent->link("autocomplete!"));

        $this->autocomplete = true;

        $this->autocompleteResults = $numOfResults;

        return $this;
    }

    /**
     * @return int
     */
    public function getAutocompleteResults() {
        return $this->autocompleteResults;
    }

    /**
     * @param bool $textarea
     * @param null|int $cols
     * @param null|int $rows
     * @return Column
     * @throws NiftyGrid\DuplicateEditableColumnException
     */
    public function setTextEditable($textarea = false, $cols = null, $rows = null): self {
        if ($this->editable) {
            throw new NiftyGrid\DuplicateEditableColumnException("Column $this->name is already editable.");
        }

        if ($textarea) {
            $input = $this->parent['gridForm'][$this->parent->name]['rowForm']->addTextArea($this->name, null, $cols, $rows);
        } else {
            $input = $this->parent['gridForm'][$this->parent->name]['rowForm']->addText($this->name, null);
        }

        $input->getControlPrototype()->addClass("grid-editable");

        $this->editable = true;

        return $this;
    }

    /**
     * @param array $values
     * @param string|null $prompt
     * @return Column
     * @throws NiftyGrid\DuplicateEditableColumnException
     */
    public function setSelectEditable(array $values, $prompt = null): self {
        if ($this->editable) {
            throw new NiftyGrid\DuplicateEditableColumnException("Column $this->name is already editable.");
        }
        $this->parent['gridForm'][$this->parent->name]['rowForm']->addSelect($this->name, null, $values)->getControlPrototype()->addClass("grid-editable");
        if ($prompt) {
            $this->parent['gridForm'][$this->parent->name]['rowForm'][$this->name]->setPrompt($prompt);
        }

        $this->editable = true;

        return $this;
    }

    /**
     * @return Column
     * @throws NiftyGrid\DuplicateEditableColumnException
     */
    public function setBooleanEditable(): self {
        if ($this->editable) {
            throw new NiftyGrid\DuplicateEditableColumnException("Column $this->name is already editable.");
        }
        $this->parent['gridForm'][$this->parent->name]['rowForm']->addCheckbox($this->name, null)->getControlPrototype()->addClass("grid-editable");

        $this->editable = true;

        return $this;
    }

    /**
     * @return Column
     * @throws NiftyGrid\DuplicateEditableColumnException
     */
    public function setDateEditable(): self {
        if ($this->editable) {
            throw new NiftyGrid\DuplicateEditableColumnException("Column $this->name is already editable.");
        }
        $this->parent['gridForm'][$this->parent->name]['rowForm']->addText($this->name, null)->getControlPrototype()->addClass("grid-datepicker")->addClass("grid-editable");

        $this->editable = true;

        return $this;
    }

    public function setSortable(bool $sortable = true): self {
        $this->sortable = $sortable;

        return $this;
    }

    public function isSortable(): bool {
        return $this->sortable;
    }

    public function hasFilter(): bool {
        return isset($this->parent['gridForm'][$this->parent->name]['filter'][$this->name]);
    }

    public function getFilterType(): string {
        return $this->filterType;
    }

    public function setTextFilter(): self {
        $this->parent['gridForm'][$this->parent->name]['filter']->addText($this->name, $this->label . ":");
        $this->filterType = FilterCondition::TEXT;

        return $this;
    }

    public function setNumericFilter(): self {
        $this->parent['gridForm'][$this->parent->name]['filter']->addText($this->name, $this->label . ":");
        $this->filterType = FilterCondition::NUMERIC;

        return $this;
    }

    /**
     * @param array $values
     * @param string $prompt
     * @return Column
     */
    public function setSelectFilter($values, $prompt = "-----"): self {
        $this->parent['gridForm'][$this->parent->name]['filter']->addSelect($this->name, $this->label . ":", $values);
        if ($prompt) {
            $this->parent['gridForm'][$this->parent->name]['filter'][$this->name]->setPrompt($prompt);
        }
        $this->filterType = FilterCondition::SELECT;

        return $this;
    }

    public function setDateFilter(): self {
        $this->parent['gridForm'][$this->parent->name]['filter']->addText($this->name, $this->label . ":")->getControlPrototype()->class("grid-datepicker");
        $this->filterType = FilterCondition::DATE;

        return $this;
    }

    /**
     * @param array $values
     * @param string $prompt
     * @return Column
     */
    public function setBooleanFilter($values = [0 => "Ne", 1 => "Ano"], $prompt = "-----"): self {
        $this->setSelectFilter($values, $prompt);
        $this->filterType = FilterCondition::BOOLEAN;

        return $this;
    }
}
