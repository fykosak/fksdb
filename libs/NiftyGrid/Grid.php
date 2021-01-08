<?php
/**
 * NiftyGrid - DataGrid for Nette
 *
 * @author    Jakub Holub
 * @copyright    Copyright (c) 2012 Jakub Holub
 * @license     New BSD Licence
 * @link        http://addons.nette.org/cs/niftygrid
 */

namespace NiftyGrid;

use Nette\Application\IPresenter;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\Container;
use Nette\ComponentModel\IComponent;
use Nette\Localization\Translator;
use NiftyGrid\Components\Column;

abstract class Grid extends \Nette\Application\UI\Control {

    const ROW_FORM = "rowForm";
    const ADD_ROW = "addRow";
    /** @persistent array */
    public $filter;
    /** @persistent string */
    public $order;
    /** @persistent int */
    public $perPage;
    /** @persistent int */
    public $activeSubGridId;
    /** @persistent string */
    public $activeSubGridName;
    /** @var array */
    protected $perPageValues = [20 => 20, 50 => 50, 100 => 100];
    public bool $paginate = true;
    /** @var string */
    protected $defaultOrder;
    /** @var DataSource\IDataSource */
    protected $dataSource;
    /** @var string */
    protected $primaryKey;
    /** @var string */
    public $gridName;
    /** @var string */
    public $width;
    /** @var bool */
    public $enableSorting = true;
    /** @var int */
    public $activeRowForm;
    /** @var callback */
    public $rowFormCallback;
    /** @var bool */
    public $showAddRow = false;
    /** @var bool */
    public $isSubGrid = false;
    /** @var array */
    public $subGrids = [];
    /** @var callback */
    public $afterConfigureSettings;
    /** @var string */
    protected $templatePath;
    /** @var string */
    public $messageNoRecords = 'Žádné záznamy';
    /** @var \Nette\Localization\ITranslator */
    protected $translator;

    public function __construct() {
        $this->monitor(IPresenter::class, function (IPresenter $presenter) {
            $this->addComponent(new Container(), "columns");
            $this->addComponent(new Container(), "buttons");
            $this->addComponent(new Container(), "globalButtons");
            $this->addComponent(new Container(), "actions");
            $this->addComponent(new Container(), "subGrids");

            if ($presenter->isAjax()) {
                $this->redrawControl();
            }

            $this->configure($presenter);

            if ($this->isSubGrid && !empty($this->afterConfigureSettings)) {
                call_user_func($this->afterConfigureSettings, $this);
            }

            if ($this->hasActiveSubGrid()) {
                $subGrid = $this->addComponent($this['subGrids']->components[$this->activeSubGridName]->getGrid(), "subGrid" . $this->activeSubGridName);
                $subGrid->registerSubGrid("subGrid" . $this->activeSubGridName);
            }

            if ($this->hasActionForm()) {
                $actions = [];
                foreach ($this['actions']->components as $name => $action) {
                    $actions[$name] = $action->getAction();
                }
                $this['gridForm'][$this->name]['action']['action_name']->setItems($actions);
            }
            if ($this->paginate) {
                if ($this->hasActiveItemPerPage()) {
                    if (in_array($this->perPage, $this['gridForm'][$this->name]['perPage']['perPage']->items)) {
                        $this['gridForm'][$this->name]['perPage']->setDefaults(["perPage" => $this->perPage]);
                    } else {
                        $items = $this['gridForm'][$this->name]['perPage']['perPage']->getItems();
                        $this->perPage = reset($items);
                    }
                } else {
                    $items = $this['gridForm'][$this->name]['perPage']['perPage']->getItems();
                    $this->perPage = reset($items);
                }
                $this->getPaginator()->itemsPerPage = $this->perPage;
            }
            if ($this->hasActiveFilter()) {
                $this->filterData();
                $this['gridForm'][$this->name]['filter']->setDefaults($this->filter);
            }
            if ($this->hasActiveOrder() && $this->hasEnabledSorting()) {
                $this->orderData($this->order);
            }
            if (!$this->hasActiveOrder() && $this->hasDefaultOrder() && $this->hasEnabledSorting()) {
                $order = explode(" ", $this->defaultOrder);
                $this->dataSource->orderData($order[0], $order[1]);
            }
        });
    }

    abstract protected function configure(Presenter $presenter): void;

    /**
     * @param string $subGrid
     */
    public function registerSubGrid($subGrid) {
        if (!$this->isSubGrid) {
            $this->subGrids[] = $subGrid;
        } else {
            $this->parent->registerSubGrid($this->name . "-" . $subGrid);
        }
    }

    /**
     * @return array
     */
    public function getSubGrids() {
        if ($this->isSubGrid) {
            return $this->parent->getSubGrids();
        } else {
            return $this->subGrids;
        }
    }

    /**
     * @param null|string $gridName
     * @return string
     */
    public function getGridPath($gridName = null) {
        if (empty($gridName)) {
            $gridName = $this->name;
        } else {
            $gridName = $this->name . "-" . $gridName;
        }
        if ($this->isSubGrid) {
            return $this->parent->getGridPath($gridName);
        } else {
            return $gridName;
        }
    }

    public function findSubGridPath($gridName) {
        foreach ($this->subGrids as $subGrid) {
            $path = explode("-", $subGrid);
            if (end($path) == $gridName) {
                return $subGrid;
            }
        }
    }

    /**
     * @param string $columnName
     * @return \Nette\Forms\IControl
     * @throws UnknownColumnException
     */
    public function getColumnInput($columnName) {
        if (!$this->columnExists($columnName)) {
            throw new UnknownColumnException("Column $columnName doesn't exists.");
        }

        return $this['gridForm'][$this->name]['rowForm'][$columnName];
    }

    /**
     * @param string $name
     * @param null|string $label
     * @param null|string $width
     * @param null|int $truncate
     * @return Components\Column
     * @return Column
     * @throws DuplicateColumnException
     */
    protected function addColumn($name, $label = null, $width = null, $truncate = null) {
        if (!empty($this['columns']->components[$name])) {
            throw new DuplicateColumnException("Column $name already exists.");
        }
        $column = new Components\Column();
        $column->setName($name)
            ->setLabel($label)
            ->setWidth($width)
            ->setTruncate($truncate)
            ->injectParent($this);
        $this['columns']->addComponent($column, $name);
        return $column;
    }

    /**
     * @param string $name
     * @param null|string $label
     * @return Components\Button
     * @throws DuplicateButtonException
     */
    protected function addButton(string $name, ?string $label = null) {
        if (!empty($this['buttons']->components[$name])) {
            throw new DuplicateButtonException("Button $name already exists.");
        }
        $button = new Components\Button();
        if ($name == self::ROW_FORM) {
            $self = $this;
            $primaryKey = $this->primaryKey;
            $button->setLink(function ($row) use ($self, $primaryKey) {
                return $self->link("showRowForm!", $row[$primaryKey]);
            });
        }
        $button->setLabel($label);
        $this['buttons']->addComponent($button, $name);
        return $button;
    }

    /**
     * @param string $name
     * @param null|string $label
     * @return Components\GlobalButton
     * @throws DuplicateGlobalButtonException
     */
    public function addGlobalButton(string $name, ?string $label = null) {
        if (!empty($this['globalButtons']->components[$name])) {
            throw new DuplicateGlobalButtonException("Global button $name already exists.");
        }
        $globalButton = new Components\GlobalButton();
        if ($name == self::ADD_ROW) {
            $globalButton->setLink($this->link("addRow!"));
        }
        $globalButton->setLabel($label);
        $this['globalButtons']->addComponent($globalButton, $name);
        return $globalButton;
    }

    /**
     * @param string $name
     * @param null|string $label
     * @return Components\Action
     * @throws DuplicateActionException
     */
    public function addAction($name, $label = null) {
        if (!empty($this['actions']->components[$name])) {
            throw new DuplicateActionException("Action $name already exists.");
        }
        $action = new Components\Action();
        $action->setName($name)
            ->setLabel($label);
        $this['actions']->addComponent($action, $name);
        return $action;
    }

    /**
     * @param string $name
     * @param null|string $label
     * @return Components\SubGrid
     * @throws DuplicateSubGridException
     */
    public function addSubGrid($name, $label = null) {
        if (!empty($this['subGrids']->components[$name]) || in_array($name, $this->getSubGrids())) {
            throw new DuplicateSubGridException("SubGrid $name already exists.");
        }
        $self = $this;
        $primaryKey = $this->primaryKey;
        $subGrid = new Components\SubGrid($this['subGrids'], $name);
        $this['subGrids']->addComponent($subGrid, $name);
        $subGrid->setName($name)
            ->setLabel($label);
        if ($this->activeSubGridName == $name) {
            $subGrid->setClass("grid-subgrid-close");
            $subGrid->setClass(function ($row) use ($self, $primaryKey) {
                return $row[$primaryKey] == $self->activeSubGridId ? "grid-subgrid-close" : "grid-subgrid-open";
            });
            $subGrid->setLink(function ($row) use ($self, $name, $primaryKey) {
                $link = $row[$primaryKey] == $self->activeSubGridId ? ["activeSubGridId" => null, "activeSubGridName" => null] : ["activeSubGridId" => $row[$primaryKey], "activeSubGridName" => $name];
                return $self->link("this", $link);
            });
        } else {
            $subGrid->setClass("grid-subgrid-open")
                ->setLink(function ($row) use ($self, $name, $primaryKey) {
                    return $self->link("this", ["activeSubGridId" => $row[$primaryKey], "activeSubGridName" => $name]);
                });
        }
        return $subGrid;
    }

    /**
     * @return array
     */
    public function getColumnNames() {
        $columns = [];
        foreach ($this['columns']->components as $column) {
            $columns[] = $column->name;
        }
        return $columns;
    }

    /**
     * @return int $count
     */
    public function getColsCount() {
        $count = count($this['columns']->components);
        if ($this->hasActionForm()) $count++;
        if ($this->hasButtons() || $this->hasFilterForm()) $count++;
        $count += count($this['subGrids']->components);

        return $count;
    }

    /**
     * @param DataSource\IDataSource $dataSource
     */
    protected function setDataSource(DataSource\IDataSource $dataSource) {
        $this->dataSource = $dataSource;
        $this->primaryKey = $this->dataSource->getPrimaryKey();
    }

    /**
     * @param string $gridName
     */
    public function setGridName($gridName) {
        $this->gridName = $gridName;
    }

    /**
     * @param string $width
     */
    public function setWidth($width) {
        $this->width = $width;
    }

    /**
     * @param string $messageNoRecords
     */
    public function setMessageNoRecords($messageNoRecords) {
        $this->messageNoRecords = $messageNoRecords;
    }

    /**
     * @param string $order
     */
    public function setDefaultOrder($order) {
        $this->defaultOrder = $order;
    }

    /**
     * @param array $values
     * @return array
     */
    protected function setPerPageValues(array $values) {
        $perPageValues = [];
        foreach ($values as $value) {
            $perPageValues[$value] = $value;
        }
        $this->perPageValues = $perPageValues;
    }

    /**
     * @return bool
     */
    public function hasButtons() {
        return count($this['buttons']->components) ? true : false;
    }

    /**
     * @return bool
     */
    public function hasGlobalButtons() {
        return count($this['globalButtons']->components) ? true : false;
    }

    /**
     * @return bool
     */
    public function hasFilterForm() {
        foreach ($this['columns']->components as $column) {
            if (!empty($column->filterType))
                return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function hasActionForm() {
        return count($this['actions']->components) ? true : false;
    }

    public function hasActiveFilter(): bool {
        return $this->filter && count($this->filter);
    }

    /**
     * @param string $filter
     * @return bool
     */
    public function isSpecificFilterActive($filter) {
        if (isset($this->filter[$filter])) {
            return ($this->filter[$filter] != '') ? true : false;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function hasActiveOrder() {
        return !empty($this->order) ? true : false;
    }

    /**
     * @return bool
     */
    public function hasDefaultOrder() {
        return !empty($this->defaultOrder) ? true : false;
    }

    /**
     * @return bool
     */
    public function hasEnabledSorting() {
        return $this->enableSorting;
    }

    /**
     * @return bool
     */
    public function hasActiveItemPerPage() {
        return !empty($this->perPage) ? true : false;
    }

    public function hasActiveRowForm() {
        return !empty($this->activeRowForm) ? true : false;
    }

    /**
     * @param string $column
     * @return bool
     */
    public function columnExists($column) {
        return isset($this['columns']->components[$column]);
    }

    /**
     * @param string $subGrid
     * @return bool
     */
    public function subGridExists($subGrid) {
        return isset($this['subGrids']->components[$subGrid]);
    }

    /**
     * @return bool
     */
    public function isEditable() {
        foreach ($this['columns']->components as $component) {
            if ($component->editable)
                return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function hasActiveSubGrid() {
        return (!empty($this->activeSubGridId) && !empty($this->activeSubGridName) && $this->subGridExists($this->activeSubGridName)) ? true : false;
    }

    /**
     * @return mixed
     * @throws InvalidFilterException
     * @throws UnknownColumnException
     * @throws UnknownFilterException
     */
    protected function filterData() {
        try {
            $filters = [];
            foreach ($this->filter as $name => $value) {
                if (!$this->columnExists($name)) {
                    throw new UnknownColumnException("Neexistující sloupec $name");
                }
                if (!$this['columns-' . $name]->hasFilter()) {
                    throw new UnknownFilterException("Neexistující filtr pro sloupec $name");
                }

                $type = $this['columns-' . $name]->getFilterType();
                $filter = FilterCondition::prepareFilter($value, $type);

                if (method_exists("\\NiftyGrid\\FilterCondition", $filter["condition"])) {
                    $filter = call_user_func("\\NiftyGrid\\FilterCondition::" . $filter["condition"], $filter["value"]);
                    if (!empty($this['gridForm'][$this->name]['filter'][$name])) {
                        $filter["column"] = $name;
                        if (!empty($this['columns-' . $filter["column"]]->tableName)) {
                            $filter["column"] = $this['columns-' . $filter["column"]]->tableName;
                        }
                        $filters[] = $filter;
                    } else {
                        throw new InvalidFilterException("Neplatný filtr");
                    }
                } else {
                    throw new InvalidFilterException("Neplatný filtr");
                }
            }
            return $this->dataSource->filterData($filters);
        } catch (UnknownColumnException $e) {
            $this->flashMessage($e->getMessage(), "grid-error");
            $this->redirect("this", ["filter" => null]);
        } catch (UnknownFilterException $e) {
            $this->flashMessage($e->getMessage(), "grid-error");
            $this->redirect("this", ["filter" => null]);
        }
    }

    /**
     * @param string $order
     * @throws InvalidOrderException
     */
    protected function orderData($order) {
        try {
            $order = explode(" ", $order);
            if (in_array($order[0], $this->getColumnNames()) && in_array($order[1], ["ASC", "DESC"]) && $this['columns-' . $order[0]]->isSortable()) {
                if (!empty($this['columns-' . $order[0]]->tableName)) {
                    $order[0] = $this['columns-' . $order[0]]->tableName;
                }
                $this->dataSource->orderData($order[0], $order[1]);
            } else {
                throw new InvalidOrderException("Neplatné seřazení.");
            }
        } catch (InvalidOrderException $e) {
            $this->flashMessage($e->getMessage(), "grid-error");
            $this->redirect("this", ["order" => null]);
        }
    }

    /**
     * @return int
     */
    protected function getCount() {
        if (!$this->dataSource) throw new GridException("DataSource not yet set");
        if ($this->paginate) {
            $count = $this->dataSource->getCount();
            $this->getPaginator()->itemCount = $count;
            $this->dataSource->limitData($this->getPaginator()->itemsPerPage, $this->getPaginator()->offset);
            return $count;
        } else {
            $count = $this->dataSource->getCount();
            $this->getPaginator()->itemCount = $count;
            return $count;
        }
    }

    /**
     * @return GridPaginator
     */
    protected function createComponentPaginator() {
        return new GridPaginator;
    }

    /**
     * @return \Nette\Utils\Paginator
     */
    public function getPaginator() {
        return $this['paginator']->paginator;
    }

    /**
     * @param int $page
     */
    public function handleChangeCurrentPage($page) {
        if ($this->presenter->isAjax()) {
            $this->redirect("this", ["paginator-page" => $page]);
        }
    }

    /**
     * @param int $perPage
     */
    public function handleChangePerPage($perPage) {
        if ($this->presenter->isAjax()) {
            $this->redirect("this", ["perPage" => $perPage]);
        }
    }

    /**
     * @param string $column
     * @param string $term
     */
    public function handleAutocomplete($column, $term) {
        if ($this->presenter->isAjax()) {
            if (!empty($this['columns']->components[$column]) && $this['columns']->components[$column]->autocomplete) {
                $this->filter[$column] = $term . "%";
                $this->filterData();
                $this->dataSource->limitData($this['columns']->components[$column]->getAutocompleteResults(), null);
                $data = $this->dataSource->getData();
                $results = [];
                foreach ($data as $row) {
                    $value = $row[$column];
                    if (!in_array($value, $results)) {
                        $results[] = $row[$column];
                    }
                }
                $this->presenter->payload->payload = $results;
                $this->presenter->sendPayload();
            }
        }
    }

    public function handleAddRow() {
        $this->showAddRow = true;
    }

    /**
     * @param int $id
     */
    public function handleShowRowForm($id) {
        $this->activeRowForm = $id;
    }

    /**
     * @param $callback
     */
    public function setRowFormCallback($callback) {
        $this->rowFormCallback = $callback;
    }

    /**
     * @param int $id
     * @return \Nette\Forms\Controls\Checkbox
     */
    public function assignCheckboxToRow($id) {
        $this['gridForm'][$this->name]['action']->addCheckbox("row_" . $id);
        $this['gridForm'][$this->name]['action']["row_" . $id]->getControlPrototype()->class[] = "grid-action-checkbox";
        return $this['gridForm'][$this->name]['action']["row_" . $id]->getControl();
    }

    protected function createComponentGridForm() {
        $form = new \Nette\Application\UI\Form;
        $form->method = "POST";
        $form->getElementPrototype()->class[] = "grid-gridForm";

        $form->addContainer($this->name);

        $form[$this->name]->addContainer("rowForm");
        $form[$this->name]['rowForm']->addSubmit("send", "Uložit");
        $form[$this->name]['rowForm']['send']->getControlPrototype()->addClass("grid-editable");

        $form[$this->name]->addContainer("filter");
        $form[$this->name]['filter']->addSubmit("send", "Filtrovat")
            ->setValidationScope(null);

        $form[$this->name]->addContainer("action");
        $form[$this->name]['action']->addSelect("action_name", "Označené:");
        $form[$this->name]['action']->addSubmit("send", "Potvrdit")
            ->setValidationScope(null)
            ->getControlPrototype()
            ->addData("select", $form[$this->name]["action"]["action_name"]->getControl()->name);

        $form[$this->name]->addContainer('perPage');
        $form[$this->name]['perPage']->addSelect("perPage", "Záznamů na stranu:", $this->perPageValues)
            ->getControlPrototype()
            ->addClass("grid-changeperpage")
            ->addData("gridname", $this->getGridPath())
            ->addData("link", $this->link("changePerPage!"));
        $form[$this->name]['perPage']->addSubmit("send", "Ok")
            ->setValidationScope(null)
            ->getControlPrototype()
            ->addClass("grid-perpagesubmit");

        $form->setTranslator($this->getTranslator());

        $form->onSuccess[] = function ($values) {
            $this->processGridForm($values);
        };

        return $form;
    }

    /**
     * @param array $values
     */
    public function processGridForm($values) {
        $values = $values->getHttpData();
        foreach ($values as $gridName => $grid) {
            foreach ($grid as $section => $container) {
                foreach ($container as $key => $value) {
                    if ($key == "send") {
                        unset($container[$key]);
                        $subGrids = $this->subGrids;
                        foreach ($subGrids as $subGrid) {
                            $path = explode("-", $subGrid);
                            if (end($path) == $gridName) {
                                $gridName = $subGrid;
                                break;
                            }
                        }
                        if ($section == "filter") {
                            $this->filterFormSubmitted($values);
                        }
                        $section = ($section == "rowForm") ? "row" : $section;
                        if (method_exists($this, $section . "FormSubmitted")) {
                            call_user_func("self::" . $section . "FormSubmitted", $container, $gridName);
                        } else {
                            $this->redirect("this");
                        }
                        break 3;
                    }
                }
            }
        }
    }

    /**
     * @param array $values
     * @param string $gridName
     */
    public function rowFormSubmitted($values, $gridName) {
        $subGrid = ($gridName == $this->name) ? false : true;
        if ($subGrid) {
            call_user_func($this[$gridName]->rowFormCallback, (array)$values);
        } else {
            call_user_func($this->rowFormCallback, (array)$values);
        }
        $this->redirect("this");
    }

    /**
     * @param array $values
     * @param string $gridName
     */
    public function perPageFormSubmitted($values, $gridName) {
        $perPage = ($gridName == $this->name) ? "perPage" : $gridName . "-perPage";

        $this->redirect("this", [$perPage => $values["perPage"]]);
    }

    /**
     * @param array $values
     * @param string $gridName
     * @throws NoRowSelectedException
     */
    public function actionFormSubmitted($values, $gridName) {
        try {
            $rows = [];
            foreach ($values as $name => $value) {
                if (\Nette\Utils\Strings::startsWith($name, "row")) {
                    $vals = explode("_", $name);
                    if ((boolean)$value) {
                        $rows[] = $vals[1];
                    }
                }
            }
            $subGrid = ($gridName == $this->name) ? false : true;
            if (!count($rows)) {
                throw new NoRowSelectedException("Nebyl vybrán žádný záznam.");
            }
            if ($subGrid) {
                call_user_func($this[$gridName]['actions']->components[$values['action_name']]->getCallback(), $rows);
            } else {
                call_user_func($this['actions']->components[$values['action_name']]->getCallback(), $rows);
            }
            $this->redirect("this");
        } catch (NoRowSelectedException $e) {
            if ($subGrid) {
                $this[$gridName]->flashMessage(_("Nebyl vybrán žádný záznam.", "grid-error"));
            } else {
                $this->flashMessage(_("Nebyl vybrán žádný záznam.", "grid-error"));
            }
            $this->redirect("this");
        }
    }

    /**
     * @param array $values
     */
    public function filterFormSubmitted($values) {
        $filters = [];
        $paginators = [];
        foreach ($values as $gridName => $grid) {
            $isSubGrid = ($gridName == $this->name) ? false : true;
            foreach ($grid['filter'] as $name => $value) {
                if ($value != '') {
                    if ($name == "send") {
                        continue;
                    }
                    if ($isSubGrid) {
                        $gridName = $this->findSubGridPath($gridName);
                        $filters[$this->name . "-" . $gridName . "-filter"][$name] = $value;
                    } else {
                        $filters[$this->name . "-filter"][$name] = $value;
                    }
                }
            }
            if ($isSubGrid) {
                $paginators[$this->name . "-" . $gridName . "-paginator-page"] = null;
                if (empty($filters[$this->name . "-" . $gridName . "-filter"])) $filters[$this->name . "-" . $gridName . "-filter"] = [];
            } else {
                $paginators[$this->name . "-paginator-page"] = null;
                if (empty($filters[$this->name . "-filter"])) $filters[$this->name . "-filter"] = [];
            }
        }
        $this->presenter->redirect("this", array_merge($filters, $paginators));
    }

    /**
     * @param string $templatePath
     */
    protected function setTemplate($templatePath) {
        $this->templatePath = $templatePath;
    }

    public function render() {
        $count = $this->getCount();
        $this->getPaginator()->itemCount = $count;
        $this->template->results = $count;
        $this->template->columns = $this['columns']->components;
        $this->template->buttons = $this['buttons']->components;
        $this->template->globalButtons = $this['globalButtons']->components;
        $this->template->subGrids = $this['subGrids']->components;
        $this->template->paginate = $this->paginate;
        $this->template->colsCount = $this->getColsCount();
        $rows = $this->dataSource->getData();
        $this->template->rows = $rows;
        $this->template->primaryKey = $this->primaryKey;
        if ($this->hasActiveRowForm()) {
            $row = $rows[$this->activeRowForm];
            foreach ($row as $name => $value) {
                if ($this->columnExists($name) && !empty($this['columns']->components[$name]->formRenderer)) {
                    $row[$name] = call_user_func($this['columns']->components[$name]->formRenderer, $row);
                }
                if (isset($this['gridForm'][$this->name]['rowForm'][$name])) {
                    $input = $this['gridForm'][$this->name]['rowForm'][$name];
                    if ($input instanceof \Nette\Forms\Controls\SelectBox) {
                        $items = $this['gridForm'][$this->name]['rowForm'][$name]->getItems();
                        if (in_array($row[$name], $items)) {
                            $row[$name] = array_search($row[$name], $items);
                        }
                    }
                }
            }
            $this['gridForm'][$this->name]['rowForm']->setDefaults($row);
            $this['gridForm'][$this->name]['rowForm']->addHidden($this->primaryKey, $this->activeRowForm);
        }
        if ($this->paginate) {
            $this->template->viewedFrom = ((($this->getPaginator()->getPage() - 1) * $this->perPage) + 1);
            $this->template->viewedTo = ($this->getPaginator()->getLength() + (($this->getPaginator()->getPage() - 1) * $this->perPage));
        }
        $templatePath = !empty($this->templatePath) ? $this->templatePath : __DIR__ . "/../../templates/grid.latte";

        if ($this->getTranslator() instanceof \Nette\Localization\ITranslator) {
            $this->template->setTranslator($this->getTranslator());
        }

        $this->template->setFile($templatePath);
        $this->template->render();
    }

    public function setTranslator(Translator $translator): self {
        $this->translator = $translator;

        return $this;
    }

    public function getTranslator(): ?Translator {
        if ($this->translator instanceof Translator) {
            return $this->translator;
        }

        return null;
    }
}
