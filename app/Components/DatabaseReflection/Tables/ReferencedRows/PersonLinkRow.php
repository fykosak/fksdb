<?php

namespace FKSDB\Components\DatabaseReflection\ReferencedRows;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\ValuePrinters\PersonLink;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Application\UI\PresenterComponent;
use FKSDB\ORM\Models\IPersonReferencedModel;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Application\BadRequestException;
use Nette\Application\LinkGenerator;
use Nette\Utils\Html;

/**
 * Class PersonLinkRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PersonLinkRow extends AbstractRow {

    private LinkGenerator $presenterComponent;

    /**
     * PersonLinkRow constructor.
     * @param LinkGenerator $presenterComponent
     */
    public function __construct(LinkGenerator $presenterComponent) {
        $this->presenterComponent = $presenterComponent;
    }

    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

    public function getTitle(): string {
        return _('Person');
    }

    /**
     * @param AbstractModelSingle|ModelPerson $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new PersonLink($this->presenterComponent))($model);
    }
}
