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

namespace NiftyGrid;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\Container;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\SelectBox;
use Nette\Localization\Translator;
use Nette\Utils\Paginator;
use Nette\Utils\Strings;
use NiftyGrid\Components\Button;
use NiftyGrid\Components\Column;
use NiftyGrid\DataSource\IDataSource;

abstract class Grid extends Control {

    public const ROW_FORM = 'rowForm';
    public const ADD_ROW = 'addRow';
    /** @persistent array */
    public array $filter = [];
    /** @persistent string */
    public ?string $order = null;
    /** @persistent int */
    public ?int $perPage = null;
    /** @persistent int */
    public ?int $activeSubGridId = null;
    /** @persistent string */
    public ?string $activeSubGridName = null;

    protected array $perPageValues = [20 => 20, 50 => 50, 100 => 100];
    public bool $paginate = true;

    protected ?string $defaultOrder = null;
    protected IDataSource $dataSource;
    protected ?string $primaryKey;

    public string $gridName;

    public ?string $width = null;

    public bool $enableSorting = true;

    public ?int $activeRowForm = null;
    /** @var callback */
    public $rowFormCallback;

    public bool $showAddRow = false;

    public bool $isSubGrid = false;

    public array $subGrids = [];
    /** @var callback */
    public $afterConfigureSettings;

    protected string $templatePath;

    public string $messageNoRecords = 'Žádné záznamy';

    protected Translator $translator;

    public function __construct() {
        $this->monitor(Presenter::class, function (Presenter $presenter) {
            $this->addComponent(new Container(), 'columns');
            $this->addComponent(new Container(), 'buttons');
            $this->addComponent(new Container(), 'globalButtons');
            $this->addComponent(new Container(), 'actions');
            $this->addComponent(new Container(), 'subGrids');

            if ($presenter->isAjax()) {
                $this->redrawControl();
            }

            $this->configure($presenter);

            if ($this->isSubGrid && isset($this->afterConfigureSettings)) {
                call_user_func($this->afterConfigureSettings, $this);
            }

            if ($this->hasActiveSubGrid()) {
                $subGrid = $this->addComponent($this->getSubGridsContainer()->components[$this->activeSubGridName]->getGrid(), 'subGrid' . $this->activeSubGridName);
                $subGrid->registerSubGrid('subGrid' . $this->activeSubGridName);
            }

            if ($this->hasActionForm()) {
                $actions = [];
                foreach ($this->getActionsContainer()->components as $name => $action) {
                    $actions[$name] = $action->getAction();
                }
                $this->getComponent('gridForm')[$this->name]['action']['action_name']->setItems($actions);
            }
            if ($this->paginate) {
                if ($this->hasActiveItemPerPage()) {
                    if (in_array($this->perPage, $this->getComponent('gridForm')[$this->name]['perPage']['perPage']->items)) {
                        $this->getComponent('gridForm')[$this->name]['perPage']->setDefaults(['perPage' => $this->perPage]);
                    } else {
                        $items = $this->getComponent('gridForm')[$this->name]['perPage']['perPage']->getItems();
                        $this->perPage = reset($items);
                    }
                } else {
                    $items = $this->getComponent('gridForm')[$this->name]['perPage']['perPage']->getItems();
                    $this->perPage = reset($items);
                }
                $this->getPaginator()->itemsPerPage = $this->perPage;
            }
            if ($this->hasActiveFilter()) {
                $this->filterData();
                $this->getComponent('gridForm')[$this->name]['filter']->setDefaults($this->filter);
            }
            if ($this->hasActiveOrder() && $this->hasEnabledSorting()) {
                $this->orderData($this->order);
            }
            if (!$this->hasActiveOrder() && $this->hasDefaultOrder() && $this->hasEnabledSorting()) {
                $order = explode(' ', $this->defaultOrder);
                $this->dataSource->orderData($order[0], $order[1]);
            }
        });
    }

    abstract protected function configure(Presenter $presenter): void;

    public function registerSubGrid(string $subGrid): void {
        if (!$this->isSubGrid) {
            $this->subGrids[] = $subGrid;
        } else {
            $this->parent->registerSubGrid($this->name . '-' . $subGrid);
        }
    }

    public function getSubGrids(): array {
        if ($this->isSubGrid) {
            return $this->parent->getSubGrids();
        } else {
            return $this->subGrids;
        }
    }

    public function getGridPath(?string $gridName = null): string {
        if (!isset($gridName)) {
            $gridName = $this->name;
        } else {
            $gridName = $this->name . '-' . $gridName;
        }
        if ($this->isSubGrid) {
            return $this->parent->getGridPath($gridName);
        } else {
            return $gridName;
        }
    }

    public function findSubGridPath(string $gridName): ?string {
        foreach ($this->subGrids as $subGrid) {
            $path = explode('-', $subGrid);
            if (end($path) == $gridName) {
                return $subGrid;
            }
        }
        return null;
    }

    /**
     * @param string $columnName
     * @return \Nette\Forms\Control
     * @throws UnknownColumnException
     */
    public function getColumnInput(string $columnName): \Nette\Forms\Control {
        if (!$this->columnExists($columnName)) {
            throw new UnknownColumnException('Column $columnName doesn\'t exists.');
        }

        return $this->getComponent('gridForm')[$this->name]['rowForm'][$columnName];
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
    protected function addColumn(string $name, ?string $label = null, ?string $width = null, ?int $truncate = null): Components\Column {
        if (isset($this->getColumnsContainer()->components[$name])) {
            throw new DuplicateColumnException('Column $name already exists.');
        }
        $column = new Components\Column();
        $column->setName($name)
            ->setLabel($label)
            ->setWidth($width)
            ->setTruncate($truncate)
            ->injectParent($this);
        $this->getColumnsContainer()->addComponent($column, $name);
        return $column;
    }

    /**
     * @param string $name
     * @param null|string $label
     * @return Components\Button
     * @throws DuplicateButtonException
     */
    protected function addButton(string $name, ?string $label = null): Button {
        if (isset($this->getButtonsContainer()->components[$name])) {
            throw new DuplicateButtonException('Button $name already exists.');
        }
        $button = new Components\Button();
        if ($name == self::ROW_FORM) {
            $self = $this;
            $primaryKey = $this->primaryKey;
            $button->setLink(function ($row) use ($self, $primaryKey) {
                return $self->link('showRowForm!', $row[$primaryKey]);
            });
        }
        $button->setLabel($label);
        $this->getButtonsContainer()->addComponent($button, $name);
        return $button;
    }

    /**
     * @param string $name
     * @param null|string $label
     * @return Components\GlobalButton
     * @throws DuplicateGlobalButtonException
     * @throws InvalidLinkException
     */
    public function addGlobalButton(string $name, ?string $label = null): Components\GlobalButton {
        if (isset($this->getGlobalButtonsContainer()->components[$name])) {
            throw new DuplicateGlobalButtonException('Global button $name already exists.');
        }
        $globalButton = new Components\GlobalButton();
        if ($name == self::ADD_ROW) {
            $globalButton->setLink($this->link('addRow!'));
        }
        $globalButton->setLabel($label);
        $this->getGlobalButtonsContainer()->addComponent($globalButton, $name);
        return $globalButton;
    }

    /**
     * @param string $name
     * @param null|string $label
     * @return Components\Action
     * @throws DuplicateActionException
     */
    public function addAction(string $name, ?string $label = null): Components\Action {
        if (isset($this->getActionsContainer()->components[$name])) {
            throw new DuplicateActionException('Action $name already exists.');
        }
        $action = new Components\Action();
        $action->setName($name)
            ->setLabel($label);
        $this->getActionsContainer()->addComponent($action, $name);
        return $action;
    }

    /**
     * @param string $name
     * @param null|string $label
     * @return Components\SubGrid
     * @throws DuplicateSubGridException
     */
    public function addSubGrid(string $name, ?string $label = null): Components\SubGrid {
        if (isset($this->getSubGridsContainer()->components[$name]) || in_array($name, $this->getSubGrids())) {
            throw new DuplicateSubGridException('SubGrid $name already exists.');
        }
        $self = $this;
        $primaryKey = $this->primaryKey;
        $subGrid = new Components\SubGrid();
        $this->getSubGridsContainer()->addComponent($subGrid, $name);
        $subGrid->setName($name)
            ->setLabel($label);
        if ($this->activeSubGridName == $name) {
            $subGrid->setClass('grid-subgrid-close');
            $subGrid->setClass(function ($row) use ($self, $primaryKey) {
                return $row[$primaryKey] == $self->activeSubGridId ? 'grid-subgrid-close' : 'grid-subgrid-open';
            });
            $subGrid->setLink(function ($row) use ($self, $name, $primaryKey) {
                $link = $row[$primaryKey] == $self->activeSubGridId ? ['activeSubGridId' => null, 'activeSubGridName' => null] : ['activeSubGridId' => $row[$primaryKey], 'activeSubGridName' => $name];
                return $self->link('this', $link);
            });
        } else {
            $subGrid->setClass('grid-subgrid-open')
                ->setLink(function ($row) use ($self, $name, $primaryKey) {
                    return $self->link('this', ['activeSubGridId' => $row[$primaryKey], 'activeSubGridName' => $name]);
                });
        }
        return $subGrid;
    }

    public function getColumnNames(): array {
        $columns = [];
        foreach ($this->getColumnsContainer()->components as $column) {
            $columns[] = $column->name;
        }
        return $columns;
    }

    /**
     * @return int $count
     */
    public function getColsCount(): int {
        $count = count($this->getColumnsContainer()->components);
        if ($this->hasActionForm()) $count++;
        if ($this->hasButtons() || $this->hasFilterForm()) $count++;
        $count += count($this->getSubGridsContainer()->components);

        return $count;
    }

    protected function setDataSource(DataSource\IDataSource $dataSource): void {
        $this->dataSource = $dataSource;
        $this->primaryKey = $this->dataSource->getPrimaryKey();
    }

    public function setGridName(string $gridName): void {
        $this->gridName = $gridName;
    }

    public function setWidth(string $width): void {
        $this->width = $width;
    }

    public function setMessageNoRecords(string $messageNoRecords): void {
        $this->messageNoRecords = $messageNoRecords;
    }

    public function setDefaultOrder(string $order): void {
        $this->defaultOrder = $order;
    }

    protected function setPerPageValues(array $values): void {
        $perPageValues = [];
        foreach ($values as $value) {
            $perPageValues[$value] = $value;
        }
        $this->perPageValues = $perPageValues;
    }

    public function hasButtons(): bool {
        return count($this->getButtonsContainer()->components) ? true : false;
    }

    public function hasGlobalButtons(): bool {
        return count($this->getGlobalButtonsContainer()->components) ? true : false;
    }

    public function hasFilterForm(): bool {
        foreach ($this->getColumnsContainer()->components as $column) {
            if (isset($column->filterType))
                return true;
        }
        return false;
    }

    public function hasActionForm(): bool {
        return count($this->getActionsContainer()->components) ? true : false;
    }

    public function hasActiveFilter(): bool {
        return $this->filter && count($this->filter);
    }

    public function isSpecificFilterActive(string $filter): bool {
        if (isset($this->filter[$filter])) {
            return $this->filter[$filter] != '';
        }
        return false;
    }

    public function hasActiveOrder(): bool {
        return isset($this->order);
    }

    public function hasDefaultOrder(): bool {
        return isset($this->defaultOrder);
    }

    public function hasEnabledSorting(): bool {
        return $this->enableSorting;
    }

    public function hasActiveItemPerPage(): bool {
        return isset($this->perPage);
    }

    public function hasActiveRowForm(): bool {
        return isset($this->activeRowForm);
    }

    public function columnExists(string $column): bool {
        return isset($this->getColumnsContainer()->components[$column]);
    }

    public function subGridExists(string $subGrid): bool {
        return isset($this->getSubGridsContainer()->components[$subGrid]);
    }

    public function isEditable(): bool {
        foreach ($this->getColumnsContainer()->components as $component) {
            if ($component->editable)
                return true;
        }
        return false;
    }

    public function hasActiveSubGrid(): bool {
        return isset($this->activeSubGridId) && isset($this->activeSubGridName) && $this->subGridExists($this->activeSubGridName);
    }

    /**
     * @return mixed
     * @throws InvalidFilterException
     */
    protected function filterData(): void {
        try {
            $filters = [];
            foreach ($this->filter as $name => $value) {
                if (!$this->columnExists($name)) {
                    throw new UnknownColumnException('Neexistující sloupec $name');
                }
                if (!$this['columns-' . $name]->hasFilter()) {
                    throw new UnknownFilterException('Neexistující filtr pro sloupec $name');
                }

                $type = $this['columns-' . $name]->getFilterType();
                $filter = FilterCondition::prepareFilter($value, $type);

                if (method_exists(FilterCondition::class, $filter['condition'])) {
                    $filter = call_user_func(FilterCondition::class . '::' . $filter['condition'], $filter['value']);
                    if (isset($this['gridForm'][$this->name]['filter'][$name])) {
                        $filter['column'] = $name;
                        if (isset($this['columns-' . $filter['column']]->tableName)) {
                            $filter['column'] = $this['columns-' . $filter['column']]->tableName;
                        }
                        $filters[] = $filter;
                    } else {
                        throw new InvalidFilterException('Neplatný filtr');
                    }
                } else {
                    throw new InvalidFilterException('Neplatný filtr');
                }
            }
            $this->dataSource->filterData($filters);
            return;
        } catch (UnknownColumnException | UnknownFilterException $e) {
            $this->flashMessage($e->getMessage(), 'grid-error');
            $this->redirect('this', ['filter' => null]);
        }
    }

    protected function orderData(string $order): void {
        try {
            $order = explode(' ', $order);
            if (in_array($order[0], $this->getColumnNames()) && in_array($order[1], ['ASC', 'DESC']) && $this['columns-' . $order[0]]->isSortable()) {
                if (isset($this['columns-' . $order[0]]->tableName)) {
                    $order[0] = $this['columns-' . $order[0]]->tableName;
                }
                $this->dataSource->orderData($order[0], $order[1]);
            } else {
                throw new InvalidOrderException('Neplatné seřazení.');
            }
        } catch (InvalidOrderException $e) {
            $this->flashMessage($e->getMessage(), 'grid-error');
            $this->redirect('this', ['order' => null]);
        }
    }

    /**
     * @return int
     * @throws GridException
     */
    protected function getCount(): int {
        if (!$this->dataSource) {
            throw new GridException('DataSource not yet set');
        }
        if ($this->paginate) {
            $count = $this->dataSource->getCount();
            $this->getPaginator()->setItemCount($count);
            $this->dataSource->limitData($this->getPaginator()->itemsPerPage, $this->getPaginator()->offset);
            return $count;
        } else {
            $count = $this->dataSource->getCount();
            $this->getPaginator()->itemCount = $count;
            return $count;
        }
    }

    protected function createComponentPaginator(): GridPaginator {
        return new GridPaginator();
    }

    public function getPaginator(): Paginator {
        return $this->getComponent('paginator')->paginator;
    }

    public function handleChangeCurrentPage(int $page): void {
        if ($this->presenter->isAjax()) {
            $this->redirect('this', ['paginator-page' => $page]);
        }
    }

    public function handleChangePerPage(int $perPage): void {
        if ($this->presenter->isAjax()) {
            $this->redirect('this', ['perPage' => $perPage]);
        }
    }

    public function handleAutocomplete(string $column, string $term): void {
        if ($this->presenter->isAjax()) {
            if (isset($this->getColumnsContainer()->components[$column]) && $this->getColumnsContainer()->components[$column]->autocomplete) {
                $this->filter[$column] = $term . '%';
                $this->filterData();
                $this->dataSource->limitData($this->getColumnsContainer()->components[$column]->getAutocompleteResults(), null);
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

    public function handleAddRow(): void {
        $this->showAddRow = true;
    }

    public function handleShowRowForm(int $id): void {
        $this->activeRowForm = $id;
    }

    public function setRowFormCallback(callable $callback): void {
        $this->rowFormCallback = $callback;
    }

    /**
     * @param int $id
     * @return Checkbox
     */
    public function assignCheckboxToRow($id): Checkbox {
        $this->getComponent('gridForm')[$this->name]['action']->addCheckbox('row_' . $id);
        $this->getComponent('gridForm')[$this->name]['action']['row_' . $id]->getControlPrototype()->class[] = 'grid-action-checkbox';
        return $this->getComponent('gridForm')[$this->name]['action']['row_' . $id]->getControl();
    }

    protected function createComponentGridForm(): Form {
        $form = new Form();
        $form->method = 'POST';
        $form->getElementPrototype()->class[] = 'grid-gridForm';

        $form->addContainer($this->name);

        $form[$this->name]->addContainer('rowForm');
        $form[$this->name]['rowForm']->addSubmit('send', 'Uložit');
        $form[$this->name]['rowForm']['send']->getControlPrototype()->addClass('grid-editable');

        $form[$this->name]->addContainer('filter');
        $form[$this->name]['filter']->addSubmit('send', 'Filtrovat')
            ->setValidationScope(null);

        $form[$this->name]->addContainer('action');
        $form[$this->name]['action']->addSelect('action_name', 'Označené:');
        $form[$this->name]['action']->addSubmit('send', 'Potvrdit')
            ->setValidationScope(null)
            ->getControlPrototype()
            ->addData('select', $form[$this->name]['action']['action_name']->getControl()->name);

        $form[$this->name]->addContainer('perPage');
        $form[$this->name]['perPage']->addSelect('perPage', 'Záznamů na stranu:', $this->perPageValues)
            ->getControlPrototype()
            ->addClass('grid-changeperpage')
            ->addData('gridname', $this->getGridPath())
            ->addData('link', $this->link('changePerPage!'));
        $form[$this->name]['perPage']->addSubmit('send', 'Ok')
            ->setValidationScope(null)
            ->getControlPrototype()
            ->addClass('grid-perpagesubmit');

        $form->setTranslator($this->getTranslator());

        $form->onSuccess[] = function (Form $values) {
            $this->processGridForm($values);
        };

        return $form;
    }

    public function processGridForm(Form $form): void {
        $values = $form->getHttpData();
        foreach ($values as $gridName => $grid) {
            foreach ($grid as $section => $container) {
                foreach ($container as $key => $value) {
                    if ($key == 'send') {
                        unset($container[$key]);
                        $subGrids = $this->subGrids;
                        foreach ($subGrids as $subGrid) {
                            $path = explode('-', $subGrid);
                            if (end($path) == $gridName) {
                                $gridName = $subGrid;
                                break;
                            }
                        }
                        if ($section == 'filter') {
                            $this->filterFormSubmitted($values);
                        }
                        $section = ($section == 'rowForm') ? 'row' : $section;
                        if (method_exists($this, $section . 'FormSubmitted')) {
                            call_user_func('self::' . $section . 'FormSubmitted', $container, $gridName);
                        } else {
                            $this->redirect('this');
                        }
                        break 3;
                    }
                }
            }
        }
    }

    public function rowFormSubmitted(array $values, string $gridName): void {
        $subGrid = ($gridName == $this->name) ? false : true;
        if ($subGrid) {
            call_user_func($this->getComponent($gridName)->rowFormCallback, (array)$values);
        } else {
            call_user_func($this->rowFormCallback, (array)$values);
        }
        $this->redirect('this');
    }

    public function perPageFormSubmitted(array $values, string $gridName): void {
        $perPage = ($gridName == $this->name) ? 'perPage' : $gridName . '-perPage';

        $this->redirect('this', [$perPage => $values['perPage']]);
    }

    public function actionFormSubmitted(array $values, string $gridName): void {
        try {
            $rows = [];
            foreach ($values as $name => $value) {
                if (Strings::startsWith($name, 'row')) {
                    $vals = explode('_', $name);
                    if ((boolean)$value) {
                        $rows[] = $vals[1];
                    }
                }
            }
            $subGrid = ($gridName == $this->name) ? false : true;
            if (!count($rows)) {
                throw new NoRowSelectedException('Nebyl vybrán žádný záznam.');
            }
            if ($subGrid) {
                call_user_func($this->getComponent($gridName)['actions']->components[$values['action_name']]->getCallback(), $rows);
            } else {
                call_user_func($this->getActionsContainer()->components[$values['action_name']]->getCallback(), $rows);
            }
            $this->redirect('this');
        } catch (NoRowSelectedException $e) {
            if ($subGrid) {
                $this->getComponent($gridName)->flashMessage(_('No row selected.'), 'grid-error');
            } else {
                $this->flashMessage(_('No row selected.'), 'grid-error');
            }
            $this->redirect('this');
        }
    }

    public function filterFormSubmitted(array $values): void {
        $filters = [];
        $paginators = [];
        foreach ($values as $gridName => $grid) {
            $isSubGrid = ($gridName == $this->name) ? false : true;
            foreach ($grid['filter'] as $name => $value) {
                if ($value != '') {
                    if ($name == 'send') {
                        continue;
                    }
                    if ($isSubGrid) {
                        $gridName = $this->findSubGridPath($gridName);
                        $filters[$this->name . '-' . $gridName . '-filter'][$name] = $value;
                    } else {
                        $filters[$this->name . '-filter'][$name] = $value;
                    }
                }
            }
            if ($isSubGrid) {
                $paginators[$this->name . '-' . $gridName . '-paginator-page'] = null;
                if (!isset($filters[$this->name . '-' . $gridName . '-filter'])) $filters[$this->name . '-' . $gridName . '-filter'] = [];
            } else {
                $paginators[$this->name . '-paginator-page'] = null;
                if (!isset($filters[$this->name . '-filter'])) $filters[$this->name . '-filter'] = [];
            }
        }
        $this->presenter->redirect('this', array_merge($filters, $paginators));
    }

    protected function setTemplate(string $templatePath): void {
        $this->templatePath = $templatePath;
    }

    /**
     * @throws GridException
     */
    public function render(): void {
        $count = $this->getCount();
        $this->getPaginator()->itemCount = $count;
        $this->template->results = $count;
        $this->template->columns = $this->getColumnsContainer()->components;
        $this->template->buttons = $this->getButtonsContainer()->components;
        $this->template->globalButtons = $this->getGlobalButtonsContainer()->components;
        $this->template->subGrids = $this->getSubGridsContainer()->components;
        $this->template->paginate = $this->paginate;
        $this->template->colsCount = $this->getColsCount();
        $rows = $this->dataSource->getData();
        $this->template->rows = $rows;
        $this->template->primaryKey = $this->primaryKey;
        if ($this->hasActiveRowForm()) {
            $row = $rows[$this->activeRowForm];
            foreach ($row as $name => $value) {
                if ($this->columnExists($name) && isset($this->getColumnsContainer()->components[$name]->formRenderer)) {
                    $row[$name] = call_user_func($this->getColumnsContainer()->components[$name]->formRenderer, $row);
                }
                if (isset($this->getComponent('gridForm')[$this->name]['rowForm'][$name])) {
                    $input = $this->getComponent('gridForm')[$this->name]['rowForm'][$name];
                    if ($input instanceof SelectBox) {
                        $items = $this->getComponent('gridForm')[$this->name]['rowForm'][$name]->getItems();
                        if (in_array($row[$name], $items)) {
                            $row[$name] = array_search($row[$name], $items);
                        }
                    }
                }
            }
            $this->getComponent('gridForm')[$this->name]['rowForm']->setDefaults($row);
            $this->getComponent('gridForm')[$this->name]['rowForm']->addHidden($this->primaryKey, $this->activeRowForm);
        }
        if ($this->paginate) {
            $this->template->viewedFrom = ((($this->getPaginator()->getPage() - 1) * $this->perPage) + 1);
            $this->template->viewedTo = ($this->getPaginator()->getLength() + (($this->getPaginator()->getPage() - 1) * $this->perPage));
        }
        $templatePath = isset($this->templatePath) ? $this->templatePath : __DIR__ . '/../../templates/grid.latte';

        if ($this->getTranslator()) {
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
        return $this->translator ?? null;
    }

    protected function getColumnsContainer(): Container {
        return $this->getComponent('columns');
    }

    protected function getButtonsContainer(): Container {
        return $this->getComponent('buttons');
    }

    protected function getGlobalButtonsContainer(): Container {
        return $this->getComponent('globalButtons');
    }

    protected function getActionsContainer(): Container {
        return $this->getComponent('actions');
    }

    protected function getSubGridsContainer(): Container {
        return $this->getComponent('subGrids');
    }
}
