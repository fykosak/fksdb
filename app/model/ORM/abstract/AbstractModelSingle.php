<?php

namespace FKSDB\ORM;

use Nette\Database\Table\ActiveRow;
use Nette\DeprecatedException;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
abstract class AbstractModelSingle extends ActiveRow implements IModel {

    /**
     * @var bool
     * @deprecated
     */
    protected $stored = true;

    /**
     * @return bool
     * @deprecated
     */
    public function isNew(): bool {
        return !$this->stored;
    }

    /**
     * @param bool $value
     * @deprecated
     */
    public function setNew(bool $value = true) {
        $this->stored = !$value;
    }

    /**
     * @param ActiveRow $row
     * @return static
     */
    public static function createFromActiveRow(ActiveRow $row): self {
        $model = new static($row->toArray(), $row->getTable());
        if ($model->getPrimary(false)) {
            $model->setNew(false);
        }
        return $model;
    }


    /**
     * @param $key
     * @param $value
     */
    public function __set($key, $value) {
        $this->update([$key => $value]);
        //Debugger::log(\sprintf('Call ActiveRow __set() with parameters %s %s.',$key, $value));
        //  return parent::__set($key, $value);
    }

}
