<?php

namespace FKSDB\Components\DatabaseReflection\PersonHistory;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\Components\DatabaseReflection\FieldLevelPermission;
use FKSDB\Components\DatabaseReflection\ValuePrinters\StringPrinter;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class AcYearRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class AcYearRow extends AbstractColumnFactory {

    public function getTitle(): string {
        return _('Academic year');
    }

    public function getPermission(): FieldLevelPermission {
        return new FieldLevelPermission(self::PERMISSION_ALLOW_BASIC, self::PERMISSION_ALLOW_BASIC);
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

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())($model->{$this->getModelAccessKey()});
    }
}
