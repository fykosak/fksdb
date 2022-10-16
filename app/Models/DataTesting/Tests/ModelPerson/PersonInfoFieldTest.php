<?php

declare(strict_types=1);

namespace FKSDB\Models\DataTesting\Tests\ModelPerson;

use FKSDB\Models\DataTesting\TestLog;
use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\Utils\Logging\Logger;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\Utils\Logging\Message;

class PersonInfoFieldTest extends PersonFileLevelTest
{
    /**
     * @throws BadTypeException
     */
    final public function run(Logger $logger, PersonModel $person): void
    {
        $info = $person->getInfo();
        if (!$info) {
            $logger->log(new TestLog($this->title, 'Person info is not set', Message::LVL_INFO));
            return;
        }
        $this->getRowFactory()->runTest($logger, $info);
    }
}
