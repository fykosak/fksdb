<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use Closure;
use FKSDB\Components\DatabaseReflection\AbstractRow;
use Nette\Forms\Form;

/**
 * Class SinceRow
 * @package FKSDB\Components\DatabaseReflection\Org
 */
class SinceRow extends AbstractRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Since');
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

    /**
     * @return Closure
     */
    public function createFieldCallback(): Closure {
        return function (int $min, int $max) {
            $control = parent::createField();
            $control->addRule(Form::NUMERIC);
            $control->addRule(Form::FILLED);
            $control->addRule(Form::RANGE, _('Počáteční ročník není v intervalu [%d, %d].'), [$min, $max]);
            return $control;
        };
    }
}
