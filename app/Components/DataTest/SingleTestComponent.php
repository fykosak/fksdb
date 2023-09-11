<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest;

use Fykosak\NetteORM\Model;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\Logging\MemoryLogger;
use Nette\DI\Container;

/**
 * @template TModel of Model
 */
class SingleTestComponent extends BaseComponent
{
    /** @phpstan-var Test<TModel> $test */
    private Test $test;

    /**
     * @phpstan-param Test<TModel> $test
     */
    public function __construct(Container $container, Test $test)
    {
        parent::__construct($container);
        $this->test = $test;
    }

    /**
     * @phpstan-param TModel $model
     */
    public function render(Model $model): void
    {
        $logger = new MemoryLogger();
        $this->test->run($logger, $model);
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'single.latte', [
            'logs' => $logger->getMessages(),
            'test' => $this->test,
        ]);
    }
}
