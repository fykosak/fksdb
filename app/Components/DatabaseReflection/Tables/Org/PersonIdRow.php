<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\ValuePrinters\PersonLink;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelOrg;
use Nette\Application\UI\PresenterComponent;
use Nette\Localization\ITranslator;
use Nette\Utils\Html;

/**
 * Class PersonIdRow
 * @package FKSDB\Components\DatabaseReflection\Org
 */
class PersonIdRow extends AbstractRow {
    /**
     * @var PresenterComponent
     */
    private $presenterComponent;

    /**
     * PersonIdRow constructor.
     * @param ITranslator $translator
     * @param PresenterComponent $presenterComponent
     */
    public function __construct(ITranslator $translator, PresenterComponent $presenterComponent) {
        parent::__construct($translator);
        $this->presenterComponent = $presenterComponent;
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Person');
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

    /**
     * @param AbstractModelSingle|ModelOrg $model
     * @param string $fieldName
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model, string $fieldName): Html {
        return (new PersonLink($this->presenterComponent))($model->getPerson());
    }

}
