<?php

namespace FKSDB\Components\DatabaseReflection\Event;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnException;
use FKSDB\Components\DatabaseReflection\FieldLevelPermission;
use FKSDB\Components\DatabaseReflection\ValuePrinters\EventRolePrinter;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\IEventReferencedModel;
use FKSDB\ORM\Models\IPersonReferencedModel;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\YearCalculator;
use Nette\Forms\Controls\BaseControl;
use Nette\Security\IUserStorage;
use Nette\Utils\Html;

class EventRole extends AbstractColumnFactory {
    /**
     * @var IUserStorage
     */
    private $userStorage;
    /**
     * @var YearCalculator
     */
    private $yearCalculator;

    /**
     * EventRole constructor.
     * @param IUserStorage $userStorage
     * @param YearCalculator $yearCalculator
     */
    public function __construct(IUserStorage $userStorage, YearCalculator $yearCalculator) {
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

    /**
     * @param AbstractModelSingle $model
     * @return AbstractModelSingle|null
     */
    protected function getModel(AbstractModelSingle $model) {
        return $model; // need to be original model because of referenced access
    }

    public function getPermission(): FieldLevelPermission {
        return new FieldLevelPermission(self::PERMISSION_ALLOW_ANYBODY, self::PERMISSION_ALLOW_ANYBODY);
    }

    public function getTitle(): string {
        return _('Event role');
    }

    public function createField(...$args): BaseControl {
        throw new AbstractColumnException();
    }
}
