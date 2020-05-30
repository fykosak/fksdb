<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\DatabaseReflection\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextArea;
use Nette\Utils\Html;

/**
 * Class TextRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TextRow extends DefaultRow {

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())($model->{$this->getModelAccessKey()});
    }

    /**
     * @param array $args
     * @return BaseControl
     */
    public function createFormControl(...$args): BaseControl {
        $control = new TextArea(_($this->getTitle()));
        $description = $this->getDescription();
        if ($description) {
            $control->setOption('description', $this->getDescription());
        }
        return $control;
    }
}
