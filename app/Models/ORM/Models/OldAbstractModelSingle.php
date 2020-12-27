<?php

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\IModel;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\InvalidStateException;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
abstract class OldAbstractModelSingle extends AbstractModelSingle implements IModel {

    private array $tmpData;

    public function __construct(array $data, Selection $table) {
        parent::__construct($data, $table);
        $this->tmpData = $data;
    }

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
    public function setNew(bool $value = true): void {
        $this->stored = !$value;
    }

    /**
     * @param ActiveRow $row
     * @return static
     * @throws InvalidStateException
     */
    public static function createFromActiveRow(ActiveRow $row): self {
        if ($row instanceof static) {
            return $row;
        }
        $model = new static($row->toArray(), $row->getTable());
        if ($model->getPrimary(false)) {
            $model->setNew(false);
        }
        return $model;
    }

    /**
     * @param string|int $key
     * @param mixed $value
     */
    public function __set($key, $value) {
        $this->tmpData[$key] = $value;
    }

    /**
     * @param int|string $key
     * @return bool|mixed|ActiveRow|Selection|null
     */
    public function &__get($key) {
        if (array_key_exists($key, $this->tmpData)) {
            return $this->tmpData[$key];
        }
        return parent::__get($key);
    }

    /**
     * @param string|int $key
     * @return bool
     */
    public function __isset($key): bool {
        if (array_key_exists($key, $this->tmpData)) {
            return true;
        }
        return parent::__isset($key);
    }

    public function getTmpData(): array {
        return $this->tmpData;
    }

    /**
     * @param string|int $key
     */
    public function __unset($key): void {
        unset($this->tmpData[$key]);
        parent::__unset($key);
    }

    public function toArray(): array {
        $data = parent::toArray();
        return array_merge($data, $this->tmpData);
    }
}
