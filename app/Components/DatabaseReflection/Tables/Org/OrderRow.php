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
 * @author Michal Červeňák <miso@fykos.cz>
 * TODO update roles
 */
class OrderRow extends AbstractOrgRowFactory {
    const ORDER_MAPPING = [
        0 => '0 - newbie',
        1 => '1 - pasivny org',
        2 => '1 - org',
        3 => '2 - aktívny org',
        4 => '4 - aktívnejši org',
        5 => '5 - Vedíci akcii  a častí seminaru',
        6 => '6 - zástupca hlavního organizátora',
        7 => '7 - hlavní organizátor',
        9 => '9 - vedoucí semináře',
    ];

    /**
     * @return string|null
     */
    public function getDescription() {
        return _('Pro řazení v seznamu organizátorů');
    }

    public function getTitle(): string {
        return _('Order');
    }

    /**
     * @param AbstractModelSingle|ModelOrg $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        if (\array_key_exists($model->order, self::ORDER_MAPPING)) {
            return (new StringPrinter())(self::ORDER_MAPPING[$model->order]);
        }
        return (new StringPrinter())($model->order);
    }

    /**
     * @param array $args
     * @return BaseControl
     */
    public function createField(...$args): BaseControl {
        $control = new SelectBox($this->getTitle());
        $control->setOption('description', $this->getDescription());
        $control->setItems(self::ORDER_MAPPING);
        $control->setPrompt(_('Zvolit hodnost'));
        $control->addRule(Form::FILLED, _('Vyberte hodnost.'));
        return $control;
    }
}
