<?php

namespace FKSDB\Components\DatabaseReflection\ValuePrinters;

use FKSDB\ORM\Models\IPersonReferencedModel;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Application\BadRequestException;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\InvalidLinkException;
use Nette\MemberAccessException;
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
     * @throws BadRequestException
     * @throws InvalidLinkException
     */
    public function getHtml($model): Html {
        $person = null;
        if ($model instanceof IPersonReferencedModel) {
            $person = $model->getPerson();
        } elseif ($model instanceof ModelPerson) {
            $person = $model;
        }
        if (!$person instanceof ModelPerson) {
            throw new BadRequestException();
        }
        return Html::el('a')
            ->addAttributes(['href' => $this->presenterComponent->link(':Common:Person:detail', [
                'id' => $person->person_id,
            ])])
            ->addText($person->getFullName());
    }
}
