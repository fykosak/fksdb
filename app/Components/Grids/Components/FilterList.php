<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components;

/**
 * @phpstan-template TModel of \Fykosak\NetteORM\Model
 * @phpstan-template TFilterParams of array
 * @phpstan-extends BaseList<TModel>
 */
abstract class FilterList extends BaseList
{
    /** @phpstan-use FilterTrait<TFilterParams> */
    use FilterTrait;

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'list.filter.latte';
    }

    public function render(): void
    {
        $this->traitRender();
        parent::render();
    }
}
