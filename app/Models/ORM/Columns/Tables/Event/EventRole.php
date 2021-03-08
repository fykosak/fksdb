<?php

namespace FKSDB\Models\ORM\Columns\Tables\Event;

use FKSDB\Models\Entity\CannotAccessModelException;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\MetaDataFactory;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\ReferencedAccessor;
use FKSDB\Models\ValuePrinters\EventRolePrinter;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\ModelEvent;
use Nette\Security\IUserStorage;
use Nette\Utils\Html;

/**
 * Class EventRole
 * @author Michal Červeňák <miso@fykos.cz>
 */
class EventRole extends ColumnFactory {

    private IUserStorage $userStorage;

    public function __construct(IUserStorage $userStorage, MetaDataFactory $metaDataFactory) {
        parent::__construct($metaDataFactory);
        $this->userStorage = $userStorage;
    }

    /**
     * @param AbstractModelSingle $model
     * @return Html
     * @throws CannotAccessModelException
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        try {
            $person = ReferencedAccessor::accessModel($model, ModelPerson::class);
        } catch (CannotAccessModelException$exception) {
            $person = $this->userStorage->getIdentity()->getPerson();
        }
        /** @var ModelEvent $event */
        $event = ReferencedAccessor::accessModel($model, ModelEvent::class);
        return (new EventRolePrinter())($person, $event);
    }

    protected function resolveModel(AbstractModelSingle $modelSingle): ?AbstractModelSingle {
        return $modelSingle; // need to be original model because of referenced access
    }
}
