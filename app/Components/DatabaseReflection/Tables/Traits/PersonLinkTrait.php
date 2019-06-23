<?php

namespace FKSDB\Components\DatabaseReflection\Tables\Traits;

use FKSDB\Components\DatabaseReflection\ValuePrinters\PersonLink;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\IPersonReferencedModel;
use Nette\Application\BadRequestException;
use Nette\Application\UI\PresenterComponent;
use Nette\Utils\Html;

/**
 * Trait PersonLinkTrait
 * @package FKSDB\Components\DatabaseReflection\Tables\Traits
 */
trait PersonLinkTrait {
    /**
     * @var PresenterComponent
     */
    protected $presenterComponent;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Person');
    }

    /**
     * @param AbstractModelSingle $model
     * @return Html
     * @throws BadRequestException
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        if (!$model instanceof IPersonReferencedModel) {
            throw new BadRequestException();
        }
        return (new PersonLink($this->presenterComponent))($model->getPerson());
    }

}
