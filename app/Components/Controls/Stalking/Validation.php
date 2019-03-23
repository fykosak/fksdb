<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\ValidationTest\Tests\ParticipantsDuration;
use FKSDB\ValidationTest\Tests\PhoneNumber;

/**
 * Class StalkingValidation
 * @package FKSDB\ValidationTest
 */
class Validation extends StalkingComponent {
    private $tests = [ParticipantsDuration::class, PhoneNumber::class];

    /**
     * @return string
     */
    protected function getHeadline(): string {
        return _('Validation');
    }

    /**
     * @return array
     */
    protected function getAllowedPermissions(): array {
        return [self::PERMISSION_RESTRICT, self::PERMISSION_FULL, self::PERMISSION_FULL];
    }

    public function render() {
        $this->beforeRender();
        $logs = [];
        foreach ($this->tests as $test) {
            $logs = \array_merge($logs, $test::run($this->modelPerson));
        }

        $this->template->logs = $logs;
        $this->template->setFile(__DIR__ . '/Validation.latte');
        $this->template->render();
    }
}
