<?php

namespace FKSDB\Components\DatabaseReflection\ReferencedRows;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\StoredQuery\ISchoolReferencedModel;
use Nette\Application\BadRequestException;
use Nette\Utils\Html;

/**
 * Class PersonLinkRow
 * *
 */
class SchoolNameRow extends AbstractRow {
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('School');
    }

    /**
     * @param AbstractModelSingle|ISchoolReferencedModel $model
     * @return Html
     * @throws BadRequestException
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        if (!$model instanceof ISchoolReferencedModel) {
            throw new BadTypeException(ISchoolReferencedModel::class, $model);
        }
        return Html::el('span')->addText($model->getSchool()->name_abbrev);
    }
}
