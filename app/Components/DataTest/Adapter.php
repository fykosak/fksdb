<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest;

use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @phpstan-template TOriginalModel of Model
 * @phpstan-template TTestedModel of Model
 * @phpstan-extends Test<TOriginalModel>
 */
abstract class Adapter extends Test
{
    /** @phpstan-var Test<TTestedModel> */
    protected Test $test;

    /**
     * @phpstan-param Test<TTestedModel> $test
     */
    public function __construct(Test $test, Container $container)
    {
        parent::__construct($container);
        $this->test = $test;
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
    final public function run(Logger $logger, Model $model): void
    {
        $models = $this->getModels($model);
        foreach ($models as $testedModel) {
            $subLogger = new MemoryLogger();
            $this->test->run($subLogger, $testedModel);
            foreach ($subLogger->getMessages() as $message) {
                $logger->log(
                    new Message(
                        $this->getLogPrepend($testedModel) . $message->text,
                        $message->level
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
     */
    abstract protected function getLogPrepend(Model $model): string;
}
