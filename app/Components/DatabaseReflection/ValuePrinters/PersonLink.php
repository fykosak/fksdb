<?php

namespace FKSDB\Components\DatabaseReflection\ValuePrinters;

use FKSDB\ORM\Models\ModelPerson;
use Nette\Application\BadRequestException;
use Nette\Application\UI\PresenterComponent;
use Nette\MemberAccessException;
use Nette\Utils\Html;

/**
 * Class PersonLink
 * @package FKSDB\Components\DatabaseReflection\ValuePrinters
 */
class PersonLink extends AbstractValuePrinter {
    /**
     * @var PresenterComponent
     */
    private $presenterComponent;

    /**
     * PersonLink constructor.
     * @param PresenterComponent $presenterComponent
     */
    public function __construct(PresenterComponent $presenterComponent) {
        $this->presenterComponent = $presenterComponent;
    }

    /**
     * @param ModelPerson $person
     * @return Html
     * @throws \Nette\Application\UI\InvalidLinkException
     * @throws BadRequestException
     */
    public function getHtml($person): Html {
        if (!$person instanceof ModelPerson) {
            throw new BadRequestException();
        }
        try {
            if ($this->presenterComponent->getPresenter()->authorized(':Common:Stalking:view', ['id' => $person->person_id])) {
                return Html::el('a')
                    ->addAttributes(['href' => $this->presenterComponent->getPresenter()->link(':Common:Stalking:view', [
                        'id' => $person->person_id,
                    ])])
                    ->addText($person->getFullName());
            }
        } catch (MemberAccessException $exception) {

        }
        return Html::el('span')
            ->addText($person->getFullName());

    }
}
