<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components;

use FKSDB\Components\Grids\Components\Button\PresenterButton;
use FKSDB\Components\Grids\Components\Container\TableRow;
use FKSDB\Components\Grids\Components\Referenced\TemplateItem;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\ORMFactory;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container as DIContainer;
use Nette\Utils\Paginator as NettePaginator;

/**
 * Combination od old NiftyGrid - Base grid from Michal Koutny
 *
 * @author    Jakub Holub
 * @copyright    Copyright (c) 2012 Jakub Holub
 * @license     New BSD Licence
 */
abstract class Grid extends BaseListComponent
{
    public bool $paginate = true;
    protected ORMFactory $tableReflectionFactory;
    public TableRow $tableRow;

    public function __construct(DIContainer $container, int $userPermission = FieldLevelPermission::ALLOW_FULL)
    {
        parent::__construct($container, $userPermission);
        $this->tableRow = new TableRow($this->container, new Title(null, ''));
        $this->addComponent($this->tableRow, 'row');
    }

    final public function injectBase(ORMFactory $tableReflectionFactory): void
    {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    protected function createComponentPaginator(): Paginator
    {
        return new Paginator($this->container);
    }

    public function getPaginator(): NettePaginator
    {
        /** @var Paginator $control */
        $control = $this->getComponent('paginator');
        return $control->paginator;
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'grid.latte';
    }

    public function render(): void
    {
        $this->template->paginate = $this->paginate;
        parent::render();
    }

    /**
     * @throws BadTypeException|\ReflectionException
     */
    protected function addColumns(array $fields): void
    {
        foreach ($fields as $name) {
            $this->addColumn(
                new TemplateItem($this->container, '@' . $name . ':value', '@' . $name . ':title'),
                str_replace('.', '__', $name)
            );
        }
    }

    protected function addColumn(ItemComponent $component, string $name): void
    {
        $this->tableRow->addComponent($component, $name);
    }
    protected function addButton(ItemComponent $component, string $name): void
    {
        $this->tableRow->buttons->addComponent($component, $name);
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
        $this->addButton($button, $name);
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
        $this->addButton($button, str_replace('.', '_', $linkId));
        return $button;
    }

    /* protected function addCSVDownloadButton(): GlobalButton
     {
        // return $this->addGlobalButton('csv', new Title(null, _('Download as csv')), 'csv!');
     }*/

    /* public function handleCsv(): void
     {
         $columns = $this->tableRow->components;
         $rows = $this->getModels();
         $data = [];
         foreach ($rows as $row) {
             $datum = [];
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
     }*/
}
