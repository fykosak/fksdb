<?php

declare(strict_types=1);

namespace FKSDB\Models\DataTesting\Tests\Person;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;

class PersonInfoFieldTest extends PersonFileLevelTest
{

    /**
     * @throws BadTypeException
     * @param PersonModel $person
     */
    final public function run(Logger $logger, Model $person): void
    {
        $info = $person->getInfo();
        if (!$info) {
            $logger->log(new Message(_('Person info is not set'), Message::LVL_INFO));
            return;
        }
        $this->getRowFactory()->runTest($logger, $info);
    }
}
