<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components;

use FKSDB\Components\Grids\Components\Referenced\SimpleItem;
use FKSDB\Components\Grids\Components\Table\TableTrait;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\FieldLevelPermission;
use Nette\DI\Container;

/**
 * Combination od old NiftyGrid - Base grid from Michal Koutny
 *
 * @author    Jakub Holub
 * @copyright    Copyright (c) 2012 Jakub Holub
 * @license     New BSD Licence
 * @phpstan-template TModel of \Fykosak\NetteORM\Model\Model
 * @phpstan-template TFilterParams of array
 * @phpstan-extends BaseComponent<TModel,TFilterParams>
 */
abstract class BaseGrid extends BaseComponent
{
    /** @phpstan-use TableTrait<TModel> */
    use TableTrait;

    public function __construct(Container $container, int $userPermission = FieldLevelPermission::ALLOW_FULL)
    {
        parent::__construct($container, $userPermission);
        $this->registerTable($container);
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'grid.latte';
    }

    /**
     * @throws BadTypeException|\ReflectionException
     * @phpstan-param string[] $fields
     */
    protected function addSimpleReferencedColumns(array $fields): void
    {
        foreach ($fields as $name) {
            /** @phpstan-ignore-next-line */
            $this->addTableColumn(
            /** @phpstan-ignore-next-line */
                new SimpleItem($this->container, $name),
                str_replace(['.', '@'], '__', $name)
            );
        }
    }

    /**
     * @phpstan-template TComponent of BaseItem<TModel>
     * @phpstan-param TComponent $component
     * @phpstan-return TComponent
     * @internal
     */
    protected function addButton(BaseItem $component, string $name): BaseItem
    {
        $this->addTableButton($component, $name);
        return $component;
    }
}
