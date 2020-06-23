<?php

namespace FKSDB\Components\DatabaseReflection\Event;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\Components\DatabaseReflection\FieldLevelPermission;
use FKSDB\Components\DatabaseReflection\OmittedControlException;
use FKSDB\Components\DatabaseReflection\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Class EventYearRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class EventYearRow extends AbstractColumnFactory {

    public function getTitle(): string {
        return _('Event year');
    }

    /**
     * @param mixed ...$args
     * @return BaseControl
     * @throws OmittedControlException
     */
    public function createField(...$args): BaseControl {
        $control = parent::createField($args);
        $control->addRule(Form::INTEGER, _('%label musí být číslo.'))
            ->addRule(Form::FILLED, _('%label je povinný.'))
            ->setOption('description', _('Ročník akce musí být unikátní pro daný typ akce.'));
        return $control;
    }

    protected function getModelAccessKey(): string {
        return 'event_year';
    }

    public function getPermission(): FieldLevelPermission {
        return new FieldLevelPermission(self::PERMISSION_ALLOW_ANYBODY, self::PERMISSION_ALLOW_ANYBODY);
    }
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())($model->{$this->getModelAccessKey()});
    }
}
