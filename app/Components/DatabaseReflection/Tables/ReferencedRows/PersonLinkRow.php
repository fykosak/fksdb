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
use Nette\Localization\ITranslator;
use Nette\Utils\Html;

/**
 * Class PersonLinkRow
 * @package FKSDB\Components\DatabaseReflection\VirtualRows
 */
class PersonLinkRow extends AbstractRow {

    /**
     * @var LinkGenerator
     */
    private $presenterComponent;

    /**
     * PersonLinkRow constructor.
     * @param ITranslator $translator
     * @param LinkGenerator $presenterComponent
     */
    public function __construct(ITranslator $translator, LinkGenerator $presenterComponent) {
        parent::__construct($translator);
        $this->presenterComponent = $presenterComponent;
    }

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
