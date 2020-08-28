<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\Services\Exception\DuplicateOrgException;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\DeprecatedLazyDBTrait;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventOrg;
use FKSDB\Exceptions\ModelException;
use FKSDB\ORM\Tables\TypedTableSelection;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * Class ServiceEventOrg
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServiceEventOrg extends AbstractServiceSingle {
    use DeprecatedLazyDBTrait;

    /**
     * ServiceEventOrg constructor.
     * @param Context $connection
     * @param IConventions $conventions
     */
    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_EVENT_ORG, ModelEventOrg::class);
    }

    /*/**
     * @param IModel|ModelEventOrg $model
     * @return void
     * @deprecated
     */
    /*public function save(IModel &$model) {
        try {
            parent::save($model);
        } catch (ModelException $exception) {
            if ($exception->getPrevious() && $exception->getPrevious()->getCode() == 23000) {
                throw new DuplicateOrgException($model->getPerson(), $exception);
            }
            throw $exception;
        }
    }*/

    public function createNewModel(array $data): ModelEventOrg {
        try {
            return parent::createNewModel($data);
        } catch (ModelException $exception) {
            if ($exception->getPrevious() && $exception->getPrevious()->getCode() == 23000) {
                throw new DuplicateOrgException(null, $exception);
            }
            throw $exception;
        }
    }

    public function findByEvent(ModelEvent $event): TypedTableSelection {
        return $this->getTable()->where('event_id', $event->event_id);
    }
}
