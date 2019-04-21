<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelOrg;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Class UntilRow
 * @package FKSDB\Components\DatabaseReflection\Org
 */
class UntilRow extends AbstractRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Until');
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

    /**
     * @param AbstractModelSingle|ModelOrg $model
     * @param string $fieldName
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model, string $fieldName): Html {
        if (\is_null($model->until)) {
            return Html::el('span')->addAttributes(['class' => 'badge badge-success'])->addText(_('Still organizes'));
        } else {
            return (new StringPrinter)($model->until);
        }
    }

    /**
     * @param int|null $min
     * @param int|null $max
     * @return BaseControl
     * @throws BadRequestException
     */
    public function createField(int $min = null, int $max = null): BaseControl {
        if (\is_null($max) || \is_null($min)) {
            throw new BadRequestException();
        }
        $control = new TextInput($this->getTitle());

        $control->addCondition(Form::FILLED);
        $control->addRule(Form::NUMERIC);
        /* ->addRule(function ($until, $since) {
             return $since->value <= $until->value;
         }, _('Konec nesmí být dříve než začátek'), $container['since'])*/
        $control->addRule(Form::RANGE, _('Koncový ročník není v intervalu [%d, %d].'), [$min, $max]);
        return $control;
    }
}
