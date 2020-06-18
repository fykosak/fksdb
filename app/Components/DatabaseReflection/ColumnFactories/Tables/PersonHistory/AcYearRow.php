<?php

namespace FKSDB\Components\DatabaseReflection\PersonHistory;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;
use FKSDB\Exceptions\NotImplementedException;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\BaseControl;

/**
 * Class AcYearRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class AcYearRow extends AbstractColumnFactory {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('Academic year');
    }

    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_BASIC;
    }

    /**
     * @param array $args
     * @return BaseControl
     * @throws BadRequestException
     */
    public function createField(...$args): BaseControl {
        throw new NotImplementedException();
    }

    protected function getModelAccessKey(): string {
        return 'ac_year';
    }
}
