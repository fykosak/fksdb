<?php

namespace FKSDB\Components\DatabaseReflection\PersonHistory;

use FKSDB\Components\Controls\Helpers\Badges\NotSetBadge;
use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPersonHistory;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class SchoolIdRow
 * @package FKSDB\Components\DatabaseReflection\PersonHistory
 */
class SchoolIdRow extends AbstractRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('School');
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_BASIC;
    }

    /**
     * @param AbstractModelSingle|ModelPersonHistory $model
     * @param string $fieldName
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model, string $fieldName): Html {
        if (is_null($model->school_id)) {
            return NotSetBadge::getHtml();
        }

        return Html::el('span')->addText($model->getSchool()->name_abbrev);
    }

    /**
     * @return BaseControl
     * @throws BadRequestException
     */
    public function createField(): BaseControl {
        throw new BadRequestException();
    }
}
