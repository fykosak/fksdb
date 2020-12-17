<?php

namespace FKSDB\Model\DBReflection\ColumnFactories\Tables\Event;

use FKSDB\Model\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\Model\DBReflection\MetaDataFactory;
use FKSDB\Model\ValuePrinters\EventRolePrinter;
use FKSDB\Model\Exceptions\BadTypeException;
use FKSDB\Model\ORM\Models\AbstractModelSingle;
use FKSDB\Model\ORM\Models\IEventReferencedModel;
use FKSDB\Model\ORM\Models\IPersonReferencedModel;
use FKSDB\Model\ORM\Models\ModelEvent;
use FKSDB\Model\YearCalculator;
use Nette\Security\IUserStorage;
use Nette\Utils\Html;

/**
 * Class EventRole
 * @author Michal Červeňák <miso@fykos.cz>
 */
class EventRole extends DefaultColumnFactory {

    private IUserStorage $userStorage;

    private YearCalculator $yearCalculator;

    public function __construct(IUserStorage $userStorage, YearCalculator $yearCalculator, MetaDataFactory $metaDataFactory) {
        parent::__construct($metaDataFactory);
        $this->userStorage = $userStorage;
        $this->yearCalculator = $yearCalculator;
    }

    /**
     * @param AbstractModelSingle $model
     * @return Html
     * @throws BadTypeException
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        if ($model instanceof IPersonReferencedModel) {
            $person = $model->getPerson();
        } else {
            $person = $this->userStorage->getIdentity()->getPerson();
        }
        if ($model instanceof IEventReferencedModel) {
            $event = $model->getEvent();
        } elseif ($model instanceof ModelEvent) {
            $event = $model;
        } else {
            throw new BadTypeException(IEventReferencedModel::class, $model);
        }
        return (new EventRolePrinter($this->yearCalculator))($person, $event);
    }

    protected function resolveModel(AbstractModelSingle $modelSingle): ?AbstractModelSingle {
        return $modelSingle; // need to be original model because of referenced access
    }
}