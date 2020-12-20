<?php

namespace FKSDB\Models\ORM\Columns\Types;

use FKSDB\Models\ValuePrinters\BinaryPrinter;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\Checkbox;
use Nette\Utils\Html;

/**
 * Class StringRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class LogicColumnFactory extends DefaultColumnFactory {

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new BinaryPrinter())($model->{$this->getModelAccessKey()});
    }

    protected function createFormControl(...$args): BaseControl {
        $control = new Checkbox(_($this->getTitle()));

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
