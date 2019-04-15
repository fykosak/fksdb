<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\ValuePrinters\PersonLink;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelEventParticipant;
use Nette\Application\UI\PresenterComponent;
use Nette\Localization\ITranslator;
use Nette\Utils\Html;

/**
 * Class PersonIdRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class PersonIdRow extends AbstractParticipantRow {
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
     * @param AbstractModelSingle|ModelEventParticipant $model
     * @param string $fieldName
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model, string $fieldName): Html {
        return (new PersonLink($this->presenterComponent))($model->getPerson());
    }
}
