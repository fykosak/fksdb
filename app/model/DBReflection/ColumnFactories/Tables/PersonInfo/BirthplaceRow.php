<?php

namespace FKSDB\DBReflection\ColumnFactories\PersonInfo;

use FKSDB\DBReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\DBReflection\FieldLevelPermission;
use FKSDB\ValuePrinters\StringPrinter;
use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPersonInfo;
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

    /**
     * @param AbstractModelSingle|ModelPersonInfo $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())($model->birthplace);
    }
}
