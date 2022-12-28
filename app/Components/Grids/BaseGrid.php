<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Grids\ListComponent\Button\PresenterButton;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\ORMFactory;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\UI\Title;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\Container;
use Nette\DI\Container as DIContainer;
use Nette\Utils\Html;
use Nette\Utils\Paginator;
use FKSDB\Components\Grids\Components\Column;
use FKSDB\Components\Grids\Components\GlobalButton;
use NiftyGrid\DataSource\IDataSource;
use PePa\CSVResponse;

/**
 * Combination od old NiftyGrid - Base grid from Michal Koutny
 *
 * @author    Jakub Holub
 * @copyright    Copyright (c) 2012 Jakub Holub
 * @license     New BSD Licence
 */
abstract class BaseGrid extends BaseComponent
{
    /** @persistent int */
    public ?int $perPage = 20;

    public bool $paginate = true;

    protected ?string $defaultOrder = null;
    protected IDataSource $dataSource;

    protected string $templatePath;

    protected ORMFactory $tableReflectionFactory;

    public function __construct(DIContainer $container)
    {
        parent::__construct($container);
        $this->monitor(Presenter::class, function (Presenter $presenter) {
            $this->addComponent(new Container(), 'columns');
            $this->addComponent(new Container(), 'buttons');
            $this->addComponent(new Container(), 'globalButtons');

            $this->configure($presenter);

            if ($this->paginate) {
                $this->getPaginator()->itemsPerPage = $this->perPage;
            }
            if (isset($this->defaultOrder)) {
                $this->dataSource->orderData($this->defaultOrder);
            }
        });
    }

    final public function injectBase(ORMFactory $tableReflectionFactory): void
    {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    protected function addColumn(string $name, string $label, callable $renderer): Column
    {
        $column = new Column($label, $renderer);
        $this->getColumnsContainer()->addComponent($column, $name);
        return $column;
    }

    public function addGlobalButton(string $name, string $label, string $link): GlobalButton
    {
        $button = new GlobalButton($label, $link);
        $this->getGlobalButtonsContainer()->addComponent($button, $name);
        $button->setClass('btn btn-sm btn-outline-primary');
        return $button;
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

    protected function setDataSource(IDataSource $dataSource): void
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

    protected function getCount(): int
    {
        $count = $this->dataSource->getCount();
        $this->getPaginator()->setItemCount($count);
        if ($this->paginate) {
            $this->dataSource->limitData($this->getPaginator()->getItemsPerPage(), $this->getPaginator()->getOffset());
        }
        return $count;
    }

    protected function createComponentPaginator(): GridPaginator
    {
        return new GridPaginator($this->container);
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

    public function getColumnsContainer(): Container
    {
        return $this->getComponent('columns');
    }

    public function getButtonsContainer(): Container
    {
        return $this->getComponent('buttons');
    }

    public function getGlobalButtonsContainer(): Container
    {
        return $this->getComponent('globalButtons');
    }


    protected function configure(Presenter $presenter): void
    {
        try {
            $this->setDataSource($this->getData());
        } catch (NotImplementedException $exception) {
        }
    }

    /**
     * @throws NotImplementedException
     */
    protected function getData(): IDataSource
    {
        throw new NotImplementedException();
    }

    public function render(): void
    {
        $paginator = $this->getPaginator();
        $count = $this->getCount();
        $this->getPaginator()->itemCount = $count;
        /*
         * Credits David Grudl.
         * @see http://addons.nette.org/cs/visualpaginator
         */
        $page = $paginator->page;
        if ($paginator->pageCount < 2) {
            $steps = [$page];
        } else {
            $arr = range(max($paginator->firstPage, $page - 3), min($paginator->lastPage, $page + 3));
            $count = 4;
            $quotient = ($paginator->pageCount - 1) / $count;
            for ($i = 0; $i <= $count; $i++) {
                $arr[] = round($quotient * $i) + $paginator->firstPage;
            }
            sort($arr);
            $steps = array_values(array_unique($arr));
        }
        $this->getComponent('paginator')->template->steps = $steps;
        $this->template->resultsCount = $this->getCount();
        $this->template->columns = $this->getColumnsContainer()->components;
        $this->template->buttons = $this->getButtonsContainer()->components;
        $this->template->globalButtons = $this->getGlobalButtonsContainer()->components;
        $this->template->paginate = $this->paginate;
        $this->template->rows = $this->dataSource->getData();
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'BaseGrid.latte');
    }

    /*     * ******************************
     * Search
     * ****************************** */

    public function isSearchable(): bool
    {
        return false;
    }



    /**
     * @throws BadTypeException
     */
    private function addReflectionColumn(string $field, int $userPermission): void
    {
        $factory = $this->tableReflectionFactory->loadColumnFactory(...explode('.', $field));
        $this->addColumn(
            str_replace('.', '__', $field),
            $factory->getTitle(),
            fn(Model $model): Html => $factory->render($model, $userPermission)
        );
    }

    /**
     * @throws BadTypeException
     */
    protected function addColumns(array $fields, int $userPermissions = FieldLevelPermission::ALLOW_FULL): void
    {
        foreach ($fields as $name) {
            $this->addReflectionColumn($name, $userPermissions);
        }
    }

    protected function addPresenterButton(
        string $destination,
        string $name,
        string $label,
        bool $checkACL = true,
        array $params = [],
        ?string $className = null
    ): PresenterButton {
        $paramMapCallback = function (Model $model) use ($params): array {
            $hrefParams = [];
            foreach ($params as $key => $value) {
                $hrefParams[$key] = $model->{$value};
            }
            return $hrefParams;
        };
        $button = new PresenterButton(
            $this->container,
            new Title(null, _($label)),
            fn(Model $model): array => [$destination, $paramMapCallback($model)],
            $className,
            fn(Model $model): bool => $checkACL ? $this->getPresenter()->authorized(
                $destination,
                $paramMapCallback($model)
            ) : true
        );
        $this->getButtonsContainer()->addComponent($button, $name);
        return $button;
    }

    /**
     * @throws BadTypeException
     */
    protected function addORMLink(string $linkId, bool $checkACL = false, ?string $className = null): PresenterButton
    {
        $factory = $this->tableReflectionFactory->loadLinkFactory(...explode('.', $linkId, 2));

        $button = new PresenterButton(
            $this->container,
            new Title(null, $factory->getText()),
            fn(Model $model): array => $factory->createLinkParameters($model),
            $className,
            fn(Model $model): bool => $checkACL
                ? $this->getPresenter()->authorized(...$factory->createLinkParameters($model))
                : true
        );
        $this->getButtonsContainer()->addComponent($button, str_replace('.', '_', $linkId));
        return $button;
    }

    /**
     * @throws InvalidLinkException
     */
    protected function addCSVDownloadButton(): GlobalButton
    {
        return $this->addGlobalButton('csv', _('Download as csv'), $this->link('csv!'));
    }

    public function handleCsv(): void
    {
        $columns = $this->getColumnsContainer()->components;
        $rows = $this->dataSource->getData();
        $data = [];
        foreach ($rows as $row) {
            $datum = [];
            /** @var Column $column */
            foreach ($columns as $column) {
                $item = $column->prepareValue($row);
                if ($item instanceof Html) {
                    $item = $item->getText();
                }
                $datum[$column->name] = $item;
            }
            $data[] = $datum;
        }
        $response = new CSVResponse($data, 'test.csv');
        $response->setAddHeading(true);
        $response->setQuotes(true);
        $response->setGlue(',');
        $this->getPresenter()->sendResponse($response);
    }
}
