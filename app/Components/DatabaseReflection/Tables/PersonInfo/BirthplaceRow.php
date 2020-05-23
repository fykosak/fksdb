<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;
use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;

/**
 * Class BirthplaceField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class BirthplaceRow extends AbstractRow {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('Místo narození');
    }

    /**
     * @return null|string
     */
    public function getDescription() {
        return _('Město a okres (kvůli diplomům).');
    }

    /**
     * @param array $args
     * @return BaseControl
     */
    public function createField(...$args): BaseControl {
        $control = new WriteOnlyInput($this->getTitle());
        $control->setOption('description', $this->getDescription());
        $control->addRule(Form::MAX_LENGTH, null, 255);
        return $control;
    }

    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_FULL;
    }

    protected function getModelAccessKey(): string {
        return 'birthplace';
    }
}
