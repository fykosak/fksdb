<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use function is_null;

/**
 * Class SinceRow
 * @package FKSDB\Components\DatabaseReflection\Org
 */
class SinceRow extends AbstractOrgRowFactory {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Since');
    }

    /**
     * @param int|null $min
     * @param int|null $max
     * @return BaseControl
     * @throws BadRequestException
     */
    public function createField(int $min = null, int $max = null): BaseControl {
        if (is_null($max) || is_null($min)) {
            throw new BadRequestException();
        }
        $control = parent::createField();
        $control->addRule(Form::NUMERIC);
        $control->addRule(Form::FILLED);
        $control->addRule(Form::RANGE, _('Počáteční ročník není v intervalu [%d, %d].'), [$min, $max]);
        return $control;
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'since';
    }
}
