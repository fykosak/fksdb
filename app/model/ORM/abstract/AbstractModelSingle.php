<?php

namespace FKSDB\ORM;

use Nette\Database\Table\ActiveRow;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
abstract class AbstractModelSingle extends ActiveRow implements IModel {
    /**
     * @var bool
     */
    protected $stored = true;

    /**
     * @return bool
     */
    public function isNew(): bool {
        return !$this->stored;
    }

    /**
     * @param bool $value
     */
    public function setNew(bool $value = true) {
        $this->stored = !$value;
    }

    /**
     * @param ActiveRow $row
     * @return static
     * @deprecated
     */
    public static function createFromTableRow(ActiveRow $row) {
        return static::createFromActiveRow($row);
    }

    /**
     * @param ActiveRow $row
     * @return static
     */
    public static function createFromActiveRow(ActiveRow $row) {
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
    public
    function __set($key, $value) {
        //Debugger::log(\sprintf('Call ActiveRow __set() with parameters %s %s.',$key, $value));
        return parent::__set($key, $value);
    }

}
