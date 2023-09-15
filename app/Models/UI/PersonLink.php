<?php

declare(strict_types=1);

namespace FKSDB\Models\UI;

use FKSDB\Models\ORM\Models\PersonModel;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\InvalidLinkException;
use Nette\Utils\Html;

class PersonLink
{
    private LinkGenerator $presenterComponent;

    public function __construct(LinkGenerator $presenterComponent)
    {
        $this->presenterComponent = $presenterComponent;
    }

    /**
     * @throws InvalidLinkException
     */
    public function __invoke(?PersonModel $value): Html
    {
        if (\is_null($value)) {
            return NotSetBadge::getHtml();
        }
        return Html::el('a')
            ->addAttributes([
                'href' => $this->presenterComponent->link('Organizer:Person:detail', [
                    'id' => $value->person_id,
                ]),
            ])
            ->addText($value->getFullName());
    }
}
