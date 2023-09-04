<?php

declare(strict_types=1);

namespace FKSDB\Models\DataTesting\Tests\Person;

use FKSDB\Components\Forms\Rules\BornNumber;
use FKSDB\Models\DataTesting\Test;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;

/**
 * @phpstan-extends Test<PersonModel>
 */
class GenderFromBornNumberTest extends Test
{
    public function __construct()
    {
        parent::__construct(_('Gender from born Id'));
    }

    /**
     * @param PersonModel $person
     */
    public function run(Logger $logger, Model $person): void
    {
        $info = $person->getInfo();

        if (!$info) {
            $logger->log(new Message(_('Person info is not set'), Message::LVL_INFO));
            return;
        }

        if (!$person->gender->value) {
            $logger->log(new Message(_('Gender is not set'), Message::LVL_WARNING));
            return;
        }
        if (!$info->born_id) {
            $logger->log(new Message(_('Born Id is not set'), Message::LVL_INFO));
            return;
        }

        if (BornNumber::getGender($info->born_id)->value != $person->gender->value) {
            $logger->log(new Message(_('Gender not match born Id'), Message::LVL_ERROR));
        } else {
            $logger->log(new Message(_('Gender match born Id'), Message::LVL_SUCCESS));
        }
    }
}
