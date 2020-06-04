<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\AbstractRowException;
use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;
use FKSDB\Components\DatabaseReflection\OmittedControlException;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;

/**
 * Class SinceRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SinceRow extends AbstractOrgRowFactory {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('Since');
    }

    /**
     * @param mixed ...$args
     * @return BaseControl
     * @throws OmittedControlException
     */
    public function createField(...$args): BaseControl {
        list($min, $max) = $args;
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
}
