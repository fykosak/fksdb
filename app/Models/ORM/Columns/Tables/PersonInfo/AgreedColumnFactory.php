<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\PersonInfo;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ValuePrinters\DatePrinter;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\ModelPersonInfo;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\Checkbox;
use Nette\Utils\Html;

class AgreedColumnFactory extends ColumnFactory {

    protected function createFormControl(...$args): BaseControl {
        $control = new Checkbox($this->getTitle());
        $link = Html::el('a');
        $link->setText(_('Agreement'));
        $link->addAttributes([
            'href' => _('http://fykos.cz/doc/souhlas.pdf'),
            'target' => '_blank',
        ]);
        $control->setOption('description', $link);
        return $control;
    }

    /**
     * @param AbstractModel|ModelPersonInfo $model
     */
    protected function createHtmlValue(AbstractModel $model): Html {
        return (new DatePrinter())($model->agreed);
    }
}
