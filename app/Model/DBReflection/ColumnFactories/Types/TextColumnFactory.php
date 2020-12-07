<?php

namespace FKSDB\Model\DBReflection\ColumnFactories\Types;

use FKSDB\Model\ValuePrinters\StringPrinter;
use FKSDB\Model\ORM\Models\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextArea;
use Nette\Utils\Html;

/**
 * Class TextRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TextColumnFactory extends DefaultColumnFactory {

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())($model->{$this->getModelAccessKey()});
    }

    protected function createFormControl(...$args): BaseControl {
        $control = new TextArea(_($this->getTitle()));
        $description = $this->getDescription();
        if ($description) {
            $control->setOption('description', $this->getDescription());
        }
        return $control;
    }
}
