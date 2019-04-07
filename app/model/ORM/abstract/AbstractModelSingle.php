<?php

namespace FKSDB\ORM;

use Nette\Database\Table\ActiveRow;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
abstract class AbstractModelSingle extends ActiveRow implements IModel {
    private $tmpData = [];

    protected $stored = true;

    /**
     * @return bool|mixed
     */
    public function isNew() {
        return !$this->stored;
    }

    /**
     * @param bool $value
     */
    public function setNew($value = true) {
        $this->stored = !$value;
    }

    /**
     * @param ActiveRow $row
     * @return static
     */
    public static function createFromTableRow(ActiveRow $row) {
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
        $this->tmpData[$key] = $value;
    }

    /**
     * @param $key
     * @return bool|mixed|ActiveRow|\Nette\Database\Table\Selection|null
     */
    public function &__get($key) {
        if (isset($this->tmpData[$key])) {
            return $this->tmpData[$key];
        }
        return parent::__get($key);
    }

    /**
     * @param $key
     * @return bool
     */
    public function __isset($key): bool {
        if (isset($this->tmpData[$key])) {
            return true;
        }
        return parent::__isset($key);
    }

    /**
     * @return array
     */
    public function getTmpData() {
        return $this->tmpData;
    }

    /**
     * @param $key
     */
    public function __unset($key) {
        unset($this->tmpData[$key]);
        return parent::__unset($key);
    }

}
