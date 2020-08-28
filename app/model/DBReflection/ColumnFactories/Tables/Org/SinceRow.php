<?php

namespace FKSDB\DBReflection\ColumnFactories\Org;

use FKSDB\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Class SinceRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SinceRow extends AbstractOrgRowFactory {
    public function getTitle(): string {
        return _('Since');
    }

    public function createField(...$args): BaseControl {
        [$min, $max] = $args;
        if (\is_null($max) || \is_null($min)) {
            throw new \InvalidArgumentException();
        }
        $control = parent::createField($args);
        $control->addRule(Form::NUMERIC);
        $control->addRule(Form::FILLED);
        $control->addRule(Form::RANGE, _('Počáteční ročník není v intervalu [%d, %d].'), [$min, $max]);
        return $control;
    }

    protected function getModelAccessKey(): string {
        return 'since';
    }

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())($model->{$this->getModelAccessKey()});
    }
}
