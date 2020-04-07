<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\DatabaseReflection\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Class StringRow
 * @package FKSDB\Components\DatabaseReflection
 */
class StringRow extends DefaultRow {
    /**
     * @param AbstractModelSingle $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter)($model->{$this->getModelAccessKey()});
    }

    /**
     * @param array $args
     * @return BaseControl
     */
    public function createField(...$args): BaseControl {
        $control = new TextInput(_($this->getTitle()));
        if ($this->getMetaData()['size']) {
            $control->addRule(Form::MAX_LENGTH, null, $this->getMetaData()['size']);
        }

        // if (!$this->metaData['nullable']) {
        // $control->setRequired();
        //  }
        $description = $this->getDescription();
        if ($description) {
            $control->setOption('description', $this->getDescription());
        }
        return $control;
    }
}
