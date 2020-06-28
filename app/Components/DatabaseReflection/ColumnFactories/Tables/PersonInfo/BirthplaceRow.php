<?php

namespace FKSDB\Components\DatabaseReflection\PersonInfo;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\Components\DatabaseReflection\FieldLevelPermission;
use FKSDB\Components\DatabaseReflection\ValuePrinters\StringPrinter;
use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Class BirthplaceRow
 * @author Michal Červeňák <miso@fykos.cz>
 * TODO to neon
 */
class BirthplaceRow extends AbstractColumnFactory {
    public function getTitle(): string {
        return _('Místo narození');
    }

    /**
     * @return string|null
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

    public function getPermission(): FieldLevelPermission {
        return new FieldLevelPermission(self::PERMISSION_ALLOW_FULL, self::PERMISSION_ALLOW_FULL);
    }

    protected function getModelAccessKey(): string {
        return 'birthplace';
    }

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())($model->{$this->getModelAccessKey()});
    }
}
