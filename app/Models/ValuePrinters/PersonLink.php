<?php

namespace FKSDB\Models\ValuePrinters;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ModelPerson;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\InvalidLinkException;
use Nette\Utils\Html;

class PersonLink extends AbstractValuePrinter
{

    private LinkGenerator $presenterComponent;

    public function __construct(LinkGenerator $presenterComponent)
    {
        $this->presenterComponent = $presenterComponent;
    }

    /**
     * @param ModelPerson|null $value
     * @return Html
     * @throws InvalidLinkException
     * @throws BadTypeException
     */
    protected function getHtml($value): Html
    {
        if (!$value instanceof ModelPerson) {
            throw new BadTypeException(ModelPerson::class, $value);
        }
        return Html::el('a')
            ->addAttributes(['href' => $this->presenterComponent->link('Org:Person:detail', [
                'id' => $value->person_id,
            ])])
            ->addText($value->getFullName());
    }
}
