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
        $this->skippedTests = $this->container->getParameters()['skippedTests'] ?? [];
        $container->callInjects($this);
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getTreeId(): string
    {
        return $this->getId();
    }
    /**
     * @phpstan-param TModel $model
     */
    final public function run(TestLogger $logger, Model $model): void
    {
        $id = $this->getId() . '(' . $model->getPrimary() . ')';
        if (in_array($id, $this->skippedTests, true)) {
            return;
        }
        $this->innerRun($logger, $model, $id);
    }

    /**
     * @phpstan-param TModel $model
     */
    abstract protected function innerRun(TestLogger $logger, Model $model, string $id): void;

    abstract public function getTitle(): Title;

    abstract public function getId(): string;
}
