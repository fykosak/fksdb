<?php

namespace FKSDB\DataTesting\Tests\Person;

use FKSDB\Components\Forms\Rules\BornNumber;
use FKSDB\Logging\ILogger;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\DataTesting\TestLog;

/**
 * Class GenderFromBornNumberTest
 * @author Michal Červeňák <miso@fykos.cz>
 */
class GenderFromBornNumberTest extends PersonTest {

    public function __construct() {
        parent::__construct('gender_from_born_number', _('Gender from born number'));
    }

    public function run(ILogger $logger, ModelPerson $person): void {
        $info = $person->getInfo();

        if (!$info) {
            // $logger->log(new TestLog($this->getTitle(), 'Person info is not set', TestLog::LVL_INFO));
            return;
        }

        if (!$person->gender) {
            $logger->log(new TestLog($this->title, _('Gender is not set'), TestLog::LVL_WARNING));
            return;
        }
        if (!$info->born_id) {
            // $logger->log(new TestLog($this->getTitle(), _('Born number is not set'), TestLog::LVL_INFO));
            return;
        }

        if (BornNumber::getGender($info->born_id) != $person->gender) {
            $logger->log(new TestLog($this->title, 'Gender not match born number', TestLog::LVL_DANGER));
        } else {
            $logger->log(new TestLog($this->title, 'Gender match born number', TestLog::LVL_SUCCESS));
        }
    }
}
