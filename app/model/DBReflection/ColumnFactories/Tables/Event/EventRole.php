<?php

namespace FKSDB\DBReflection\ColumnFactories\Event;

use FKSDB\DBReflection\ColumnFactories\DefaultColumnFactory;
use FKSDB\DBReflection\MetaDataFactory;
use FKSDB\ValuePrinters\EventRolePrinter;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\IEventReferencedModel;
use FKSDB\ORM\Models\IPersonReferencedModel;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\YearCalculator;
use Nette\Security\IUserStorage;
use Nette\Utils\Html;

/**
 * Class EventRole
 * @author Michal Červeňák <miso@fykos.cz>
 */
class EventRole extends DefaultColumnFactory {

    private IUserStorage $userStorage;

    private YearCalculator $yearCalculator;

    /**
     * EventRole constructor.
     * @param IUserStorage $userStorage
     * @param YearCalculator $yearCalculator
     * @param MetaDataFactory $metaDataFactory
     */
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

    protected function resolveModel(AbstractModelSingle $model): ?AbstractModelSingle {
        return $model; // need to be original model because of referenced access
    }
}
