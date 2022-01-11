<?php

namespace FKSDB\Models\DataTesting\Tests\ModelPerson;

use FKSDB\Components\Forms\Rules\BornNumber;
use Fykosak\Utils\Logging\Logger;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\DataTesting\TestLog;
use Fykosak\Utils\Logging\Message;

class GenderFromBornNumberTest extends PersonTest {

    public function __construct() {
        parent::__construct('gender_from_born_number', _('Gender from born number'));
    }

    public function run(Logger $logger, ModelPerson $person): void {
        $info = $person->getInfo();

        if (!$info) {
            $logger->log(new TestLog($this->title, 'Person info is not set', TestLog::LVL_SKIP));
            return;
        }

        if (!$person->gender) {
            $logger->log(new TestLog($this->title, _('Gender is not set'), Message::LVL_WARNING));
            return;
        }
        if (!$info->born_id) {
            $logger->log(new TestLog($this->title, _('Born number is not set'), TestLog::LVL_SKIP));
            return;
        }

        if (BornNumber::getGender($info->born_id) != $person->gender) {
            $logger->log(new TestLog($this->title, 'Gender not match born number', Message::LVL_ERROR));
        } else {
            $logger->log(new TestLog($this->title, 'Gender match born number', Message::LVL_SUCCESS));
        }
    }
}
