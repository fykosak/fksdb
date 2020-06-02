<?php

namespace FKSDB\Components\DatabaseReflection\Event;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\AbstractRowException;
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

class EventRole extends AbstractRow {
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
        $person = null;

        if ($model instanceof IPersonReferencedModel) {
            $person = $model->getPerson();
        } else {
            $person = $this->userStorage->getIdentity()->getPerson();
        }
        $event = null;
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
        return $model; // must to be original model because of
    }

    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

    public function getTitle(): string {
        return _('Event role');
    }

    /**
     * @param mixed ...$args
     * @return BaseControl
     * @throws AbstractRowException
     */
    public function createField(...$args): BaseControl {
        throw new AbstractRowException();
    }
}
