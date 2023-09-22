<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components;

use FKSDB\Components\Grids\Components\Button\Button;
use FKSDB\Components\Grids\Components\Referenced\TemplateItem;
use FKSDB\Components\Grids\Components\TableRow\TableRow;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\FieldLevelPermission;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;
use Nette\Utils\Paginator as NettePaginator;

/**
 * Combination od old NiftyGrid - Base grid from Michal Koutny
 *
 * @author    Jakub Holub
 * @copyright    Copyright (c) 2012 Jakub Holub
 * @license     New BSD Licence
 * @phpstan-template TModel of \Fykosak\NetteORM\Model
 * @phpstan-extends BaseComponent<TModel>
 */
abstract class BaseGrid extends BaseComponent
{
    /** @phpstan-var TableRow<TModel> */
    public TableRow $tableRow;

    public function __construct(Container $container, int $userPermission = FieldLevelPermission::ALLOW_FULL)
    {
        parent::__construct($container, $userPermission);
        $this->tableRow = new TableRow($this->container);
        $this->addComponent($this->tableRow, 'row');
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'grid.latte';
    }

    /**
     * @throws BadTypeException|\ReflectionException
     * @phpstan-param string[] $fields
     */
    protected function addColumns(array $fields): void
    {
        foreach ($fields as $name) {
            /** @phpstan-ignore-next-line */
            $this->addColumn(
                new TemplateItem($this->container, '@' . $name . ':value', '@' . $name . ':title'),
                str_replace('.', '__', $name)
            );
        }
    }

    /**
     * @phpstan-template TComponent of BaseItem<TModel>
     * @phpstan-param TComponent $component
     * @phpstan-return TComponent
     */
    protected function addColumn(BaseItem $component, string $name): BaseItem
    {
        $this->tableRow->addColumn($component, $name);
        return $component;
    }

    /**
     * @phpstan-template TComponent of BaseItem<TModel>
     * @phpstan-param TComponent $component
     * @phpstan-return TComponent
     */
    protected function addButton(BaseItem $component, string $name): BaseItem
    {
        $this->tableRow->addButton($component, $name);
        return $component;
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
