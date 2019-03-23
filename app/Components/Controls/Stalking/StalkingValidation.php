<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\ValidationTest\ParticipantsDurationTest;

/**
 * Class StalkingValidation
 * @package FKSDB\ValidationTest
 */
class StalkingValidation extends StalkingComponent {
    private $tests = [ParticipantsDurationTest::class];

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
            $logs += $test::run($this->modelPerson);
        }

        $this->template->logs = $logs;
        $this->template->setFile(__DIR__ . '/StalkingValidation.latte');
        $this->template->render();
    }
}
