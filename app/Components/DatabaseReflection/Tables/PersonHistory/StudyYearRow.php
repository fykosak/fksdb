<?php

namespace FKSDB\Components\DatabaseReflection\PersonHistory;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\BaseControl;

/**
 * Class StudyYearRow
 * @package FKSDB\Components\DatabaseReflection\PersonHistory
 */
class StudyYearRow extends AbstractRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Study year');
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_BASIC;
    }

    /**
     * @return BaseControl
     * @throws BadRequestException
     */
    public function createField(): BaseControl {
        throw new BadRequestException();
    }
}
