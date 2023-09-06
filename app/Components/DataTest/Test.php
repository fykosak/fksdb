<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest;

use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\UI\Title;

/**
 * @template TModel of Model
 */
abstract class Test
{
    /**
     * @phpstan-param TModel $model
     */
    abstract public function run(Logger $logger, Model $model): void;

    abstract public function getTitle(): Title;
}
