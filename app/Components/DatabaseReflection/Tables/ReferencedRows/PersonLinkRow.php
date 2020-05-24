<?php

namespace FKSDB\Components\DatabaseReflection\ReferencedRows;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\ValuePrinters\PersonLink;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\IPersonReferencedModel;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Application\BadRequestException;
use Nette\Application\LinkGenerator;
use Nette\Utils\Html;

/**
 * Class PersonLinkRow
 * *
 */
class PersonLinkRow extends AbstractRow {

    /**
     * @var LinkGenerator
     */
    private $presenterComponent;

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
     * @param AbstractModelSingle|ModelPerson|IPersonReferencedModel $model
     * @return Html
     * @throws BadRequestException
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        $person = null;
        if ($model instanceof ModelPerson) {
            $person = $model;
        } elseif ($model instanceof IPersonReferencedModel) {
            $person = $model->getPerson();
        }
        if (!$person) {
            throw new BadTypeException(IPersonReferencedModel::class, $model);
        }
        return (new PersonLink($this->presenterComponent))($person);
    }
}
