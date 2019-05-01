<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\Tables\Traits\PersonLinkTrait;
use Nette\Application\UI\PresenterComponent;
use Nette\Localization\ITranslator;

/**
 * Class PersonIdRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class PersonIdRow extends AbstractParticipantRow {
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
}
