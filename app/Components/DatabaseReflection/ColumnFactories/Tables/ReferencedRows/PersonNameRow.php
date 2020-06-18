<?php

namespace FKSDB\Components\DatabaseReflection\ReferencedRows;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\Components\DatabaseReflection\ValuePrinters\StringPrinter;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\IPersonReferencedModel;
use Nette\Application\BadRequestException;
use Nette\Utils\Html;

/**
 * Class PersonNameRowFactory
 * *
 */
class PersonNameRow extends AbstractColumnFactory {

    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

    public function getTitle(): string {
        return _('Person');
    }

    /**
     * @param AbstractModelSingle $model
     * @return Html
     * @throws BadRequestException
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        if (!$model instanceof IPersonReferencedModel) {
            throw new BadTypeException(IPersonReferencedModel::class, $model);
        }
        return (new StringPrinter())($model->getPerson()->getFullName());
    }
}
