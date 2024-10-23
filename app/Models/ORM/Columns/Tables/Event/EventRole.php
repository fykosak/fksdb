<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Event;

use FKSDB\Models\ORM\Columns\Types\AbstractColumnFactory;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\UI\EventRolePrinter;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\Model\Model;
use Nette\Security\User;
use Nette\Utils\Html;

/**
 * @phpstan-extends AbstractColumnFactory<Model>
 */
class EventRole extends AbstractColumnFactory
{
    private User $user;

    final public function injectUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @throws CannotAccessModelException
     * @throws \ReflectionException
     */
    protected function createHtmlValue(Model $model): Html
    {
        try {
            $person = $model->getReferencedModel(PersonModel::class);
        } catch (CannotAccessModelException$exception) {
            /** @var LoginModel|null $login */
            $login = $this->user->getIdentity();
            $person = $login->person;
        }
        /** @var EventModel $event */
        $event = $model->getReferencedModel(EventModel::class);
        return EventRolePrinter::getHtml($person, $event);
    }

    protected function resolveModel(Model $modelSingle): Model
    {
        return $modelSingle; // need to be original model because of referenced access
    }
}
