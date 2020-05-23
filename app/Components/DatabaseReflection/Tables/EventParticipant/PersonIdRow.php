<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;
use Nette\Application\UI\PresenterComponent;

/**
 * Class PersonIdRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class PersonIdRow extends AbstractParticipantRow {
    use DefaultPrinterTrait;

    /**
     * PersonIdRow constructor.
     * @param PresenterComponent $presenterComponent
     */
    public function __construct(PresenterComponent $presenterComponent) {
        $this->presenterComponent = $presenterComponent;
    }

    public function getTitle(): string {
        return _('Person info');
    }

    protected function getModelAccessKey(): string {
        return 'person_info';
    }
}
