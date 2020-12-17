<?php

namespace FKSDB\Model\ValuePrinters;

use FKSDB\Model\Exceptions\BadTypeException;
use FKSDB\Model\ORM\Models\ModelPerson;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\InvalidLinkException;
use Nette\Utils\Html;

/**
 * Class PersonLink
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PersonLink extends AbstractValuePrinter {

    private LinkGenerator $presenterComponent;

    public function __construct(LinkGenerator $presenterComponent) {
        $this->presenterComponent = $presenterComponent;
    }

    /**
     * @param ModelPerson|null $person
     * @return Html
     * @throws InvalidLinkException
     * @throws BadTypeException
     */
    protected function getHtml($person): Html {
        if (!$person instanceof ModelPerson) {
            throw new BadTypeException(ModelPerson::class, $person);
        }
        return Html::el('a')
            ->addAttributes(['href' => $this->presenterComponent->link('Org:Person:detail', [
                'id' => $person->person_id,
            ])])
            ->addText($person->getFullName());
    }
}
