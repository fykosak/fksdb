<?php

declare(strict_types=1);

namespace FKSDB\Models\ValuePrinters;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\PersonModel;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\InvalidLinkException;
use Nette\Utils\Html;

class PersonLink extends ValuePrinter
{
    private LinkGenerator $presenterComponent;

    public function __construct(LinkGenerator $presenterComponent)
    {
        $this->presenterComponent = $presenterComponent;
    }

    /**
     * @param PersonModel|null $value
     * @throws InvalidLinkException
     * @throws BadTypeException
     */
    protected function getHtml($value): Html
    {
        if (!$value instanceof PersonModel) {
            throw new BadTypeException(PersonModel::class, $value);
        }
        return Html::el('a')
            ->addAttributes(['href' => $this->presenterComponent->link('Org:Person:detail', [
                'id' => $value->person_id,
            ])])
            ->addText($value->getFullName());
    }
}
