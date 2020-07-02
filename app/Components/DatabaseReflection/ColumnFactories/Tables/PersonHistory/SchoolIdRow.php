<?php

namespace FKSDB\Components\DatabaseReflection\PersonHistory;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\Components\DatabaseReflection\FieldLevelPermission;
use FKSDB\Components\Forms\Factories\SchoolFactory;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPersonHistory;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class SchoolIdRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SchoolIdRow extends AbstractColumnFactory {
    /** @var SchoolFactory */
    private $schoolFactory;

    /***
     * SchoolIdRow constructor.
     * @param SchoolFactory $schoolFactory
     */
    public function __construct(SchoolFactory $schoolFactory) {
        $this->schoolFactory = $schoolFactory;
    }

    public function getTitle(): string {
        return _('School');
    }

    public function getPermission(): FieldLevelPermission {
        return new FieldLevelPermission(self::PERMISSION_ALLOW_BASIC, self::PERMISSION_ALLOW_BASIC);
    }

    /**
     * @param AbstractModelSingle|ModelPersonHistory $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        if (is_null($model->school_id)) {
            return NotSetBadge::getHtml();
        }
        return Html::el('span')->addText('#' . $model->school_id);
    }

    public function createField(...$args): BaseControl {
        return $this->schoolFactory->createSchoolSelect();
    }
}
