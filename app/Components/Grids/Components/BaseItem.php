<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components;

use FKSDB\Models\Exceptions\GoneException;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-template TModel of \Fykosak\NetteORM\Model
 */
abstract class BaseItem extends BaseComponent
{
    /**
     * @phpstan-param TModel $model
     * @note do not call from parent
     * @throws GoneException
     */
    abstract public function render(Model $model, int $userPermission): void;

    abstract public function getTitle(): ?Title;
}
