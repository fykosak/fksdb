<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests;

use FKSDB\Components\DataTest\TestLogger;
use FKSDB\Components\DataTest\TestMessage;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\UI\Title;
use Nette\Application\LinkGenerator;
use Nette\DI\Container;
use Nette\Utils\Html;

/**
 * @phpstan-template TOriginalModel of Model
 * @phpstan-template TTestedModel of Model
 * @phpstan-extends Test<TOriginalModel>
 */
abstract class Adapter extends Test
{
    /** @phpstan-var Test<TTestedModel> */
    protected Test $test;
    protected LinkGenerator $linkGenerator;

    /**
     * @phpstan-param Test<TTestedModel> $test
     */
    public function __construct(Test $test, Container $container)
    {
        parent::__construct($container);
        $this->test = $test;
    }

    public function inject(LinkGenerator $linkGenerator): void
    {
        $this->linkGenerator = $linkGenerator;
    }

    final public function getTitle(): Title
    {
        return $this->test->getTitle();
    }

    final public function getDescription(): ?string
    {
        return $this->test->getDescription();
    }

    /**
     * @param TOriginalModel $model
     */
    final public function run(TestLogger $logger, Model $model, string $id): void
    {
        $models = $this->getModels($model);
        foreach ($models as $testedModel) {
            $subLogger = new TestLogger();
            $this->test->run(
                $subLogger,
                $testedModel,
                $this->test->getId()
            );
            foreach ($subLogger->getMessages() as $message) {
                $logger->log(
                    new TestMessage(
                        $this->getId() . sprintf('(%d)', $testedModel->getPrimary()) . '-' . $message->id,
                        $this->getLogPrepend($testedModel),
                        $message->level,
                        $message,
                    )
                );
            }
        }
    }

    /**
     * @phpstan-return TTestedModel[]
     * @phpstan-param TOriginalModel $model
     */
    abstract protected function getModels(Model $model): iterable;

    /**
     * @phpstan-param TTestedModel $model
     * @return Html|string
     */
    abstract protected function getLogPrepend(Model $model);
}
