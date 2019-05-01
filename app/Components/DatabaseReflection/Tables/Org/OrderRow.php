<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelOrg;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Class OrderRow
 * @package FKSDB\Components\DatabaseReflection\Org
 */
class OrderRow extends AbstractOrgRowFactory {
    const ORDER_MAPPING = [
        0 => '0 - org',
        1 => '1',
        2 => '2',
        3 => '3',
        4 => '4 - hlavní organizátor',
        9 => '9 - vedoucí semináře',
    ];

    /**
     * @return string|null
     */
    public function getDescription() {
        return _('Pro řazení v seznamu organizátorů');
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Order');
    }

    /**
     * @param AbstractModelSingle|ModelOrg $model
     * @param string $fieldName
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model, string $fieldName): Html {
        if (\array_key_exists($model->order, self::ORDER_MAPPING)) {
            return (new StringPrinter)(self::ORDER_MAPPING[$model->order]);
        }
        return (new StringPrinter)($model->order);
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        $control = new SelectBox($this->getTitle());
        $control->setOption('description', $this->getDescription());
        $control->setItems(self::ORDER_MAPPING);
        $control->setPrompt(_('Zvolit hodnost'));
        $control->addRule(Form::FILLED, _('Vyberte hodnost.'));
        return $control;
    }
}
