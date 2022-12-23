<?php

declare(strict_types=1);

namespace NiftyGrid;

use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\Container;
use Nette\InvalidStateException;
use Nette\Localization\Translator;
use Nette\Utils\Paginator;
use NiftyGrid\Components\Button;
use NiftyGrid\Components\Column;
use NiftyGrid\DataSource\IDataSource;

/**
 * NiftyGrid - DataGrid for Nette
 *
 * @author    Jakub Holub
 * @copyright    Copyright (c) 2012 Jakub Holub
 * @license     New BSD Licence
 * @link        http://addons.nette.org/cs/niftygrid
 */
abstract class Grid extends Control
{
    /** @persistent string */
    public ?string $order = null;
    /** @persistent int */
    public ?int $perPage = 20;

    public bool $paginate = true;

    protected ?string $defaultOrder = null;
    protected IDataSource $dataSource;

    public bool $enableSorting = true;

    /** @var callback */
    public $afterConfigureSettings;

    protected string $templatePath;

    protected Translator $translator;

    public function __construct()
    {
        $this->monitor(Presenter::class, function (Presenter $presenter) {
            $this->addComponent(new Container(), 'columns');
            $this->addComponent(new Container(), 'buttons');
            $this->addComponent(new Container(), 'globalButtons');

            if ($presenter->isAjax()) {
                $this->redrawControl();
            }

            $this->configure($presenter);

            if ($this->paginate) {
                $this->getPaginator()->itemsPerPage = $this->perPage;
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

    protected function addColumn(string $name, string $label): Components\Column
    {
        $column = new Components\Column($label);
        $this->getColumnsContainer()->addComponent($column, $name);
        return $column;
    }

    protected function addButton(string $name, string $label, callable $link): Button
    {
        $button = new Components\Button($label, $link);
        $this->getButtonsContainer()->addComponent($button, $name);
        return $button;
    }

    public function addGlobalButton(string $name, string $label, string $link): Components\GlobalButton
    {
        $globalButton = new Components\GlobalButton($label, $link);
        $this->getGlobalButtonsContainer()->addComponent($globalButton, $name);
        return $globalButton;
    }

    public function getColumnNames(): array
    {
        $columns = [];
        foreach ($this->getColumnsContainer()->components as $column) {
            $columns[] = $column->name;
        }
        return $columns;
    }

    public function getColsCount(): int
    {
        $count = count($this->getColumnsContainer()->components);
        if ($this->hasButtons()) {
            $count++;
        }

        return $count;
    }

    protected function setDataSource(DataSource\IDataSource $dataSource): void
    {
        $this->dataSource = $dataSource;
    }

    public function setDefaultOrder(string $order): void
    {
        $this->defaultOrder = $order;
    }

    public function hasButtons(): bool
    {
        return count($this->getButtonsContainer()->components) > 0;
    }

    public function hasGlobalButtons(): bool
    {
        return count($this->getGlobalButtonsContainer()->components) > 0;
    }

    public function hasActiveOrder(): bool
    {
        return isset($this->order);
    }

    public function hasDefaultOrder(): bool
    {
        return isset($this->defaultOrder);
    }

    public function hasEnabledSorting(): bool
    {
        return $this->enableSorting;
    }

    public function columnExists(string $column): bool
    {
        return isset($this->getColumnsContainer()->components[$column]);
    }

    protected function orderData(string $order): void
    {
        try {
            $order = explode(' ', $order);
            if (
                in_array($order[0], $this->getColumnNames()) && in_array($order[1], ['ASC', 'DESC']
                ) && $this['columns-' . $order[0]]->isSortable()
            ) {
                $this->dataSource->orderData($order[0], $order[1]);
            } else {
                throw new InvalidStateException('Neplatné seřazení.');
            }
        } catch (InvalidStateException $e) {
            $this->flashMessage($e->getMessage(), 'grid-error');
            $this->redirect('this', ['order' => null]);
        }
    }

    protected function getCount(): int
    {
        $count = $this->dataSource->getCount();
        $this->getPaginator()->setItemCount($count);
        if ($this->paginate) {
            $this->dataSource->limitData($this->getPaginator()->itemsPerPage, $this->getPaginator()->offset);
        }
        return $count;
    }

    protected function createComponentPaginator(): GridPaginator
    {
        return new GridPaginator();
    }

    public function getPaginator(): Paginator
    {
        return $this->getComponent('paginator')->paginator;
    }

    public function handleChangeCurrentPage(int $page): void
    {
        if ($this->presenter->isAjax()) {
            $this->redirect('this', ['paginator-page' => $page]);
        }
    }

    protected function setTemplate(string $templatePath): void
    {
        $this->templatePath = $templatePath;
    }

    public function render(): void
    {
        $count = $this->getCount();
        $this->getPaginator()->itemCount = $count;
        $this->template->results = $count;
        $this->template->columns = $this->getColumnsContainer()->components;
        $this->template->buttons = $this->getButtonsContainer()->components;
        $this->template->globalButtons = $this->getGlobalButtonsContainer()->components;
        $this->template->paginate = $this->paginate;
        $this->template->colsCount = $this->getColsCount();
        $this->template->rows = $this->dataSource->getData();
        if ($this->paginate) {
            $this->template->viewedFrom = $this->getPaginator()->getFirstItemOnPage();
            $this->template->viewedTo = $this->getPaginator()->getLastItemOnPage();
        }
        if ($this->getTranslator()) {
            $this->template->setTranslator($this->getTranslator());
        }

        $this->template->render($this->templatePath ?? __DIR__ . '/../../templates/grid.latte');
    }

    public function setTranslator(Translator $translator): void
    {
        $this->translator = $translator;
    }

    public function getTranslator(): ?Translator
    {
        return $this->translator ?? null;
    }

    protected function getColumnsContainer(): Container
    {
        return $this->getComponent('columns');
    }

    protected function getButtonsContainer(): Container
    {
        return $this->getComponent('buttons');
    }

    protected function getGlobalButtonsContainer(): Container
    {
        return $this->getComponent('globalButtons');
    }
}
