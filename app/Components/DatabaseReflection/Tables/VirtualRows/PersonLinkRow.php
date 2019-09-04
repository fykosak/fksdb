<?php

namespace FKSDB\Components\DatabaseReflection\VirtualRows;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\Tables\Traits\PersonLinkTrait;
use Nette\Application\UI\PresenterComponent;
use Nette\Localization\ITranslator;

/**
 * Class PersonLinkRow
 * @package FKSDB\Components\DatabaseReflection\VirtualRows
 */
class PersonLinkRow extends AbstractRow {
    use PersonLinkTrait;

    /**
     * PersonLinkRow constructor.
     * @param ITranslator $translator
     * @param PresenterComponent $presenterComponent
     */
    public function __construct(ITranslator $translator, PresenterComponent $presenterComponent) {
        parent::__construct($translator);
        $this->presenterComponent=$presenterComponent;
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }
}
