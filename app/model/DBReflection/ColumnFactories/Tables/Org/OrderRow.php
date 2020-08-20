<?php

namespace FKSDB\DBReflection\ColumnFactories\Org;

use FKSDB\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelOrg;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Class OrderRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class OrderRow extends AbstractOrgRowFactory {
    public const ORDER_MAPPING = [
        0 => 'pasivní org',
        1 => 'org',
        2 => 'aktivní org',
        3 => '',
        4 => 'vedení',
        5 => '',
        6 => 'zástupce hlavního organizátora',
        7 => 'hlavní organizátor',
        8 => '',
        9 => 'vedoucí semináře',
    ];

    public function getDescription(): ?string {
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

    public function createField(...$args): BaseControl {
        $control = new SelectBox($this->getTitle());
        $control->setOption('description', $this->getDescription());
        $control->setItems(self::ORDER_MAPPING);
        $control->setPrompt(_('Select rank'));
        $control->addRule(Form::FILLED, _('Please select rank.'));
        return $control;
    }
}
