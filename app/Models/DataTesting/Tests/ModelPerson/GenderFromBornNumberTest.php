<?php

declare(strict_types=1);

namespace FKSDB\Models\DataTesting\Tests\ModelPerson;

use FKSDB\Components\Forms\Rules\BornNumber;
use FKSDB\Models\DataTesting\TestLog;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;

class GenderFromBornNumberTest extends PersonTest
{
    public function __construct()
    {
        parent::__construct('gender_from_born_number', _('Gender from born ID'));
    }

    public function run(Logger $logger, PersonModel $person): void
    {
        $info = $person->getInfo();

        if (!$info) {
            $logger->log(new TestLog($this->title, _('Person info is not set'), TestLog::LVL_SKIP));
            return;
        }

        if (!$person->gender->value) {
            $logger->log(new TestLog($this->title, _('Gender is not set'), Message::LVL_WARNING));
            return;
        }
        if (!$info->born_id) {
            $logger->log(new TestLog($this->title, _('Born ID is not set'), TestLog::LVL_SKIP));
            return;
        }

        if (BornNumber::getGender($info->born_id)->value != $person->gender->value) {
            $logger->log(new TestLog($this->title, _('Gender not match born ID'), Message::LVL_ERROR));
        } else {
            $logger->log(new TestLog($this->title, _('Gender match born ID'), Message::LVL_SUCCESS));
        }
    }
}
