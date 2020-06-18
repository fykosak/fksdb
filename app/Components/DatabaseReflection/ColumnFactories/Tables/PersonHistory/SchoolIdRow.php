<?php

namespace FKSDB\Components\DatabaseReflection\PersonHistory;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPersonHistory;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class SchoolIdRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SchoolIdRow extends AbstractColumnFactory {

    public function getTitle(): string {
        return _('School');
    }


    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_BASIC;
    }

    /**
     * @param AbstractModelSingle|ModelPersonHistory $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        if (is_null($model->school_id)) {
            return NotSetBadge::getHtml();
        }

        return Html::el('span')->addText($model->getSchool()->name_abbrev);
    }

    /**
     * @param array $args
     * @return BaseControl
     * @throws BadRequestException
     */
    public function createField(...$args): BaseControl {
        throw new NotImplementedException();
    }
}
