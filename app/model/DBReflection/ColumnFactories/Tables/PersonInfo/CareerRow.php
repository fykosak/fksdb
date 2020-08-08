<?php

namespace FKSDB\DBReflection\ColumnFactories\PersonInfo;

use FKSDB\DBReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\DBReflection\FieldLevelPermission;
use FKSDB\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextArea;
use Nette\Utils\Html;

/**
 * Class CareerRow
 * @author Michal Červeňák <miso@fykos.cz>
 * TODO to neon
 */
class CareerRow extends AbstractColumnFactory {

    public function getTitle(): string {
        return _('Co právě dělá');
    }

    /**
     * @return null|string
     */
    public function getDescription() {
        return _('Zobrazeno v seznamu organizátorů');
    }

    /**
     * @param array $args
     * @return BaseControl
     */
    public function createField(...$args): BaseControl {
        $control = new TextArea($this->getTitle());
        $control->setOption('description', $this->getDescription());
        return $control;
    }

    public function getPermission(): FieldLevelPermission {
        return new FieldLevelPermission(self::PERMISSION_ALLOW_BASIC, self::PERMISSION_ALLOW_BASIC);
    }

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())($model->career);
    }
}
