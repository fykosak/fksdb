<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest;

use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Logger;

/**
 * @template TModel of Model
 */
abstract class Test
{
    public string $title;

    public function __construct(string $title)
    {
        $this->title = $title;
    }

    /**
     * @phpstan-param TModel $model
     */
    abstract public function run(Logger $logger, Model $model): void;
}
