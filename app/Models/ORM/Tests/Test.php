<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests;

use FKSDB\Components\DataTest\TestLogger;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @phpstan-template TModel of Model
 */
abstract class Test
{
    protected Container $container;
    /** @var string[] */
    protected array $skippedTests;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->skippedTests = $this->container->getParameters()['skippedTests'];
        $container->callInjects($this);
    }

    public function getDescription(): ?string
    {
        return null;
    }

    /**
     * @phpstan-param TModel $model
     */
    abstract public function run(TestLogger $logger, Model $model): void;

    abstract public function getTitle(): Title;

    abstract public function getId(): string;

    /**
     * @phpstan-param TModel $model
     */
    protected function formatId(Model $model): string
    {
        return $this->getId() . '(' . $model->getPrimary() . ')';
    }

    /**
     * @phpstan-param TModel $model
     */
    protected function skip(Model $model): bool
    {
        foreach ($this->skippedTests as $test) {
            if ($this->formatId($model) === $test) {
                return true;
            }
        }
        return false;
    }
}
