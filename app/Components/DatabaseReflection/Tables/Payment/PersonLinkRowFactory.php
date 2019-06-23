<?php

namespace FKSDB\Components\DatabaseReflection\Payment;

use FKSDB\Components\DatabaseReflection\Tables\Traits\PersonLinkTrait;
use Nette\Application\UI\PresenterComponent;
use Nette\Localization\ITranslator;

/**
 * Class PersonLinkRowFactrory
 * @package FKSDB\Components\DatabaseReflection\Payment
 */
class PersonLinkRowFactory extends AbstractPaymentRow {
    use PersonLinkTrait;

    /**
     * PersonLinkRowFactory constructor.
     * @param PresenterComponent $presenterComponent
     * @param ITranslator $translator
     */
    public function __construct(PresenterComponent $presenterComponent, ITranslator $translator) {
        parent::__construct($translator);
        $this->presenterComponent = $presenterComponent;
    }
}
