<?php

namespace FKSDB\Components\DatabaseReflection\PersonHistory;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\BaseControl;

/**
 * Class AcYearRow
 * @package FKSDB\Components\DatabaseReflection\PersonHistory
 */
class AcYearRow extends AbstractRow {
    use DefaultPrinterTrait;
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Academic year');
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

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'ac_year';
    }
}
