<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest\Tests\Person;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;

class PersonInfoFieldTest extends PersonFileLevelTest
{

    /**
     * @throws BadTypeException
     * @param PersonModel $model
     */
    final public function run(Logger $logger, Model $model): void
    {
        $info = $model->getInfo();
        if (!$info) {
            return;
        }
        $this->getRowFactory()->runTest($logger, $info);
    }
}
