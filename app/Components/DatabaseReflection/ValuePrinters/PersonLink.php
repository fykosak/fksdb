<?php

namespace FKSDB\Components\DatabaseReflection\ValuePrinters;

use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\PresenterComponent;
use Nette\Utils\Html;

/**
 * Class PersonLink
 * @author Michal Červeňák <miso@fykos.cz>
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
     * @param ModelPerson|null $person
     * @return Html
     * @throws InvalidLinkException
     * @throws BadRequestException
     */
    public function getHtml($person): Html {
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
