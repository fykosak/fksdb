<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components;

/**
 * @phpstan-template TModel of \Fykosak\NetteORM\Model
 * @phpstan-template TFilterParams of array
 * @phpstan-extends BaseGrid<TModel>
 */
abstract class FilterGrid extends BaseGrid
{
    /** @phpstan-use FilterTrait<TFilterParams> */
    use FilterTrait;

    public function render(): void
    {
        $this->traitRender();
        parent::render();
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'grid.filter.latte';
    }
}
