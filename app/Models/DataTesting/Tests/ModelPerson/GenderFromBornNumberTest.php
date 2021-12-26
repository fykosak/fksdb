<?php

namespace FKSDB\Models\DataTesting\Tests\ModelPerson;

use FKSDB\Components\Forms\Rules\BornNumber;
use FKSDB\Models\DataTesting\TestLogLevel;
use Fykosak\Utils\Logging\Logger;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\DataTesting\TestLog;

class GenderFromBornNumberTest extends PersonTest {

    public function __construct() {
        parent::__construct('gender_from_born_number', _('Gender from born number'));
    }

    public function run(Logger $logger, ModelPerson $person): void {
        $info = $person->getInfo();

        if (!$info) {
            $logger->log(new TestLog($this->title, 'Person info is not set', TestLogLevel::SKIP));
            return;
        }

        if (!$person->gender) {
            $logger->log(new TestLog($this->title, _('Gender is not set'), TestLogLevel::WARNING));
            return;
        }
        if (!$info->born_id) {
            $logger->log(new TestLog($this->title, _('Born number is not set'), TestLogLevel::SKIP));
            return;
        }

        if (BornNumber::getGender($info->born_id) != $person->gender) {
            $logger->log(new TestLog($this->title, 'Gender not match born number', TestLogLevel::ERROR));
        } else {
            $logger->log(new TestLog($this->title, 'Gender match born number', TestLogLevel::SUCCESS));
        }
    }
}
