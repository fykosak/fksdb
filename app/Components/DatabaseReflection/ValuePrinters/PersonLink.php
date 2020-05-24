<?php

namespace FKSDB\Components\DatabaseReflection\ValuePrinters;

use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\IPersonReferencedModel;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Application\BadRequestException;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\InvalidLinkException;
use Nette\Utils\Html;

/**
 * Class PersonLink
 * @package FKSDB\Components\DatabaseReflection\ValuePrinters
 */
class PersonLink extends AbstractValuePrinter {
    /**
     * @var LinkGenerator
     */
    private $presenterComponent;

    /**
     * PersonLink constructor.
     * @param LinkGenerator $presenterComponent
     */
    public function __construct(LinkGenerator $presenterComponent) {
        $this->presenterComponent = $presenterComponent;
    }

    /**
     * @param null $model
     * @return Html
     * @throws InvalidLinkException
     * @throws BadRequestException
     */
    public function getHtml($model): Html {
        $person = null;
        if ($model instanceof IPersonReferencedModel) {
            $person = $model->getPerson();
        } elseif ($model instanceof ModelPerson) {
            $person = $model;
        }
        if (!$person instanceof ModelPerson) {
            throw new BadTypeException(ModelPerson::class, $person);
        }
        return Html::el('a')
            ->addAttributes(['href' => $this->presenterComponent->link('Common:Person:detail', [
                'id' => $person->person_id,
            ])])
            ->addText($person->getFullName());
    }
}
