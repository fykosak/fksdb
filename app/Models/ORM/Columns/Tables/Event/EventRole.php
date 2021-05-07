<?php

namespace FKSDB\Models\ORM\Columns\Tables\Event;

use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\MetaDataFactory;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\ReferencedAccessor;
use FKSDB\Models\ValuePrinters\EventRolePrinter;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\ModelEvent;
use Nette\Security\User;
use Nette\Utils\Html;

/**
 * Class EventRole
 * @author Michal Červeňák <miso@fykos.cz>
 */
class EventRole extends ColumnFactory {

    private User $user;

    public function __construct(User $user, MetaDataFactory $metaDataFactory) {
        parent::__construct($metaDataFactory);
        $this->user = $user;
    }

    /**
     * @param AbstractModel $model
     * @return Html
     * @throws CannotAccessModelException
     */
    protected function createHtmlValue(AbstractModel $model): Html {
        try {
            $person = ReferencedAccessor::accessModel($model, ModelPerson::class);
        } catch (CannotAccessModelException$exception) {
            $login = $this->user->getIdentity();
            $person = $login->getPerson();
        }
        /** @var ModelEvent $event */
        $event = ReferencedAccessor::accessModel($model, ModelEvent::class);
        return (new EventRolePrinter())($person, $event);
    }

    protected function resolveModel(AbstractModel $modelSingle): ?AbstractModel {
        return $modelSingle; // need to be original model because of referenced access
    }
}
