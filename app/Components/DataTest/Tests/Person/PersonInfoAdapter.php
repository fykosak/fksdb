<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest\Tests\Person;

use FKSDB\Components\DataTest\Test;
use FKSDB\Models\ORM\Models\PersonInfoModel;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @phpstan-extends Test<PersonModel>
 */
class PersonInfoAdapter extends Test
{
    /** @phpstan-var Test<PersonInfoModel> */
    private Test $test;

    /**
     * @phpstan-param Test<PersonInfoModel> $test
     */
    public function __construct(Test $test, Container $container)
    {
        parent::__construct($container);
        $this->test = $test;
    }

    public function getTitle(): Title
    {
        return $this->test->getTitle();
    }

    public function getDescription(): ?string
    {
        return $this->test->getDescription();
    }

    /**
     * @param PersonModel $model
     */
    final public function run(Logger $logger, Model $model): void
    {
        $info = $model->getInfo();
        if (!$info) {
            return;
        }
        $this->test->run($logger, $info);
    }
}
