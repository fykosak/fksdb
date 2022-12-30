<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Grids\ListComponent\Button\PresenterButton;
use FKSDB\Components\Grids\ListComponent\Container\TableRow;
use FKSDB\Components\Grids\ListComponent\ItemComponent;
use FKSDB\Components\Grids\ListComponent\Referenced\TemplateItem;
use FKSDB\Components\Grids\ListComponent\Renderer\RendererItem;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\ORMFactory;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\NetteORM\TypedSelection;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\UI\Title;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\Container;
use Nette\Database\Table\Selection;
use Nette\DI\Container as DIContainer;
use Nette\Utils\Paginator;
use FKSDB\Components\Grids\Components\GlobalButton;
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
    public bool $paginate = true;

    protected ORMFactory $tableReflectionFactory;
    /** @var TypedSelection|TypedGroupedSelection */
    protected Selection $data;

    public function __construct(DIContainer $container)
    {
        parent::__construct($container);
        $this->addComponent(new TableRow($this->container, new Title(null, '')), 'columns');
        $this->addComponent(new Container(), 'globalButtons');

        $this->monitor(Presenter::class, fn() => $this->configure());
    }

    final public function injectBase(ORMFactory $tableReflectionFactory): void
    {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    protected function addColumn(string $name, Title $title, callable $renderer): RendererItem
    {
        $column = new RendererItem($this->container, $renderer, $title);
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

    public function hasGlobalButtons(): bool
    {
        return count($this->getGlobalButtonsContainer()->components) > 0;
    }

    protected function getCount(): int
    {
        $count = $this->data->count('*');
        $this->getPaginator()->setItemCount($count);
        if ($this->paginate) {
            $this->data->limit($this->getPaginator()->getItemsPerPage(), $this->getPaginator()->getOffset());
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

    public function getColumnsContainer(): TableRow
    {
        return $this->getComponent('columns');
    }

    public function getGlobalButtonsContainer(): Container
    {
        return $this->getComponent('globalButtons');
    }


    abstract protected function configure(): void;

    public function render(): void
    {
        $this->getPaginator()->itemCount = $this->getCount();
        $this->template->resultsCount = $this->getCount();
        $this->template->rows = $this->data;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    private function addReflectionColumn(string $field): void
    {
        $this->getColumnsContainer()->addComponent(
            new TemplateItem($this->container, '@' . $field . ':value', '@' . $field . ':title'),
            str_replace('.', '__', $field)
        );
    }

    /**
     * @throws BadTypeException|\ReflectionException
     */
    protected function addColumns(array $fields): void
    {
        foreach ($fields as $name) {
            $this->addReflectionColumn($name);
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
        $this->getColumnsContainer()->getButtonContainer()->addComponent($button, $name);
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
        $this->getColumnsContainer()->getButtonContainer()->addComponent($button, str_replace('.', '_', $linkId));
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
        $rows = $this->data;
        $data = [];
        foreach ($rows as $row) {
            $datum = [];
            /** @var ItemComponent $column */
            foreach ($columns as $column) {
                //$column->render($row, 1024);
                // TODO
                //  $item = $column->prepareValue($row);
                // if ($item instanceof Html) {
                //    $item = $item->getText();
                //}
                //$datum[$column->name] = $item;
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
