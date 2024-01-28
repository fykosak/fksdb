<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components;

use FKSDB\Models\Exceptions\GoneException;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\BaseComponent\BaseComponent;

/**
 * @phpstan-template TModel of \Fykosak\NetteORM\Model\Model
 */
abstract class BaseItem extends BaseComponent
{
    /**
     * @phpstan-param TModel $model
     * @throws GoneException
     */
    abstract public function render(Model $model, int $userPermission): void;

    abstract public function renderTitle(): void;
}
