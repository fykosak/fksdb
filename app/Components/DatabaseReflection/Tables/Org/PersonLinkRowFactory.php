<?php

namespace FKSDB\Components\DatabaseReflection\Org;

use FKSDB\Components\DatabaseReflection\AbstractRowException;
use FKSDB\Components\DatabaseReflection\Tables\Traits\PersonLinkTrait;
use Nette\Application\UI\PresenterComponent;
use Nette\Forms\Controls\BaseControl;
use Nette\Localization\ITranslator;

/**
 * Class PersonIdRow
 * @package FKSDB\Components\DatabaseReflection\Org
 */
class PersonLinkRowFactory extends AbstractOrgRowFactory {
    use PersonLinkTrait;

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
     * @return BaseControl
     * @throws AbstractRowException
     */
    public function createField(): BaseControl {
        throw new AbstractRowException();
    }
}
