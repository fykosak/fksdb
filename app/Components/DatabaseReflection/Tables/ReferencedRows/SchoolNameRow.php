<?php

namespace FKSDB\Components\DatabaseReflection\ReferencedRows;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\ValuePrinters\EventLink;
use FKSDB\Components\DatabaseReflection\ValuePrinters\PersonLink;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\IEventReferencedModel;
use FKSDB\ORM\Models\IPersonReferencedModel;
use FKSDB\ORM\Models\StoredQuery\ISchoolReferencedModel;
use Nette\Application\BadRequestException;
use Nette\Application\UI\PresenterComponent;
use Nette\Localization\ITranslator;
use Nette\Utils\Html;

/**
 * Class PersonLinkRow
 * @package FKSDB\Components\DatabaseReflection\VirtualRows
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
            throw new BadRequestException();
        }
        return Html::el('span')->addText($model->getSchool()->name_abbrev);
    }
}
