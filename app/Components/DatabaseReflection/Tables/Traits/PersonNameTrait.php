<?php

namespace FKSDB\Components\DatabaseReflection\Tables\Traits;

use FKSDB\Components\DatabaseReflection\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\IPersonReferencedModel;
use Nette\Application\BadRequestException;
use Nette\Utils\Html;

/**
 * Trait PersonNameTrait
 * @package FKSDB\Components\DatabaseReflection
 */
trait PersonNameTrait {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Person');
    }

    /**
     * @param AbstractModelSingle $model
     * @param string $fieldName
     * @return Html
     * @throws BadRequestException
     */
    protected function createHtmlValue(AbstractModelSingle $model, string $fieldName): Html {
        if (!$model instanceof IPersonReferencedModel) {
            throw new BadRequestException();
        }
        return (new StringPrinter)($model->getPerson()->getFullName());
    }
}
