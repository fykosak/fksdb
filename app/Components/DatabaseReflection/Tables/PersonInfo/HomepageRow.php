<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;
use FKSDB\Components\Forms\Controls\URLTextBox;
use Nette\Forms\Controls\BaseControl;

/**
 * Class HomepageField
 * *
 */
class HomepageRow extends AbstractRow {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('Homepage');
    }

    /**
     * @param array $args
     * @return BaseControl
     */
    public function createField(...$args): BaseControl {
        return new URLTextBox();
    }

    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_BASIC;
    }

    protected function getModelAccessKey(): string {
        return 'homepage';
    }
}
