<?php

namespace FKSDB\Components\DatabaseReflection\ValuePrinters;

use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\IPersonReferencedModel;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\PresenterComponent;
use Nette\MemberAccessException;
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
     * @param ModelPerson $person
     * @return Html
     * @throws InvalidLinkException
     * @throws BadRequestException
     */
    public function getHtml($person): Html {
        if (!$person instanceof ModelPerson) {
            throw new BadTypeException(ModelPerson::class, $person);
        }
        try {
            if ($this->presenterComponent->getPresenter()->authorized(':Common:Person:detail', ['id' => $person->person_id])) {
                return Html::el('a')
                    ->addAttributes(['href' => $this->presenterComponent->getPresenter()->link(':Common:Person:detail', [
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
