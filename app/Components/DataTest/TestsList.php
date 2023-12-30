<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest;

use FKSDB\Models\ORM\Tests\Test;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\DI\Container;

/**
 * @phpstan-template TModel of Model
 */
class TestsList extends BaseComponent
{
    /** @phpstan-var Test<TModel>[] */
    private array $tests;

    /**
     * @phpstan-param Test<TModel>[] $tests
     */
    public function __construct(Container $container, array $tests)
    {
        parent::__construct($container);
        $this->tests = $tests;
    }

    /**
     * @phpstan-param TModel $model
     */
    public function render(Model $model, bool $list = true): void
    {
        $data = [];
        foreach ($this->tests as $test) {
            $logger = new TestLogger();
            $test->run($logger, $model, '');
            if (count($logger->getMessages())) {
                $data[] = [
                    'messages' => $logger->getMessages(),
                    'test' => $test,
                ];
            }
        }
        if ($list) {
            $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'list.latte', ['data' => $data]);
        } else {
            $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'noList.latte', ['data' => $data]);
        }
    }
}
