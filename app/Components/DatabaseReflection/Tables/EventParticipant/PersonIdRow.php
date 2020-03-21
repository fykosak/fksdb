<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;
use Nette\Application\UI\PresenterComponent;
use Nette\Localization\ITranslator;

/**
 * Class PersonIdRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class PersonIdRow extends AbstractParticipantRow {
    use DefaultPrinterTrait;

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
        return _('Person info');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'person_info';
    }
}
