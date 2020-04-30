<?php

namespace FKSDB\Components\DatabaseReflection\ValuePrinters;

use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\IPersonReferencedModel;
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
     * @param ModelPerson|IPersonReferencedModel $model
     * @return Html
     * @throws \Nette\Application\UI\InvalidLinkException
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
