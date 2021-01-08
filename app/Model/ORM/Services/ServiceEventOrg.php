<?php

namespace FKSDB\Model\ORM\Services;

use FKSDB\Model\Exceptions\ModelException;
use FKSDB\Model\ORM\DbNames;
use FKSDB\Model\ORM\Models\AbstractModelSingle;
use FKSDB\Model\ORM\Models\ModelEvent;
use FKSDB\Model\ORM\Models\ModelEventOrg;
use FKSDB\Model\ORM\Services\Exceptions\DuplicateOrgException;
use FKSDB\Model\ORM\Tables\TypedTableSelection;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * Class ServiceEventOrg
 * @author Michal Červeňák <miso@fykos.cz>
 * @method ModelEventOrg refresh(AbstractModelSingle $model)
 */
class ServiceEventOrg extends AbstractServiceSingle {
    use DeprecatedLazyService;

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

    public function store(?ModelEventOrg $model, array $data): ModelEventOrg {
        if (is_null($model)) {
            return $this->createNewModel($data);
        } else {
            $this->updateModel2($model, $data);
            return $this->refresh($model);
        }
    }
}
