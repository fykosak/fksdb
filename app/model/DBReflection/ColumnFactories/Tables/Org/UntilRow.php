<?php

namespace FKSDB\DBReflection\ColumnFactories\Org;

use FKSDB\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelOrg;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Class UntilRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class UntilRow extends AbstractOrgRowFactory {

    public function getTitle(): string {
        return _('Until');
    }

    /**
     * @param AbstractModelSingle|ModelOrg $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        if (\is_null($model->until)) {
            return Html::el('span')->addAttributes(['class' => 'badge badge-success'])->addText(_('Still organizes'));
        } else {
            return (new StringPrinter())($model->until);
        }
    }

    public function createField(...$args): BaseControl {
        [$min, $max] = $args;
        if (\is_null($max) || \is_null($min)) {
            throw new \InvalidArgumentException();
        }
        $control = new TextInput($this->getTitle());

        $control->addCondition(Form::FILLED)
            ->addRule(Form::NUMERIC)
            ->addRule(Form::RANGE, _('Koncový ročník není v intervalu [%d, %d].'), [$min, $max]);
        return $control;
    }
}
