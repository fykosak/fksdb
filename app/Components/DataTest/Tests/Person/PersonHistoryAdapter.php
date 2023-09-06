<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest\Tests\Person;

use FKSDB\Components\DataTest\Test;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<PersonModel>
 */
class PersonHistoryAdapter extends Test
{
    /** @phpstan-var Test<PersonHistoryModel> */
    private Test $test;

    /**
     * @phpstan-param Test<PersonHistoryModel> $test
     */
    public function __construct(Test $test)
    {
        $this->test = $test;
    }

    public function getTitle(): Title
    {
        return $this->test->getTitle();
    }

    /**
     * @param PersonModel $model
     */
    public function run(Logger $logger, Model $model): void
    {
        $histories = $model->getHistories();
        /** @var PersonHistoryModel $history */
        foreach ($histories as $history) {
            $this->test->run($logger, $history);
        }
    }
}
