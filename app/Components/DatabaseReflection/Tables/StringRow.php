<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\DatabaseReflection\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Utils\Html;

/**
 * Class StringRow
 * @package FKSDB\Components\DatabaseReflection
 */
class StringRow extends AbstractRow {
    /**
     * @var string
     */
    private $title;
    /**
     * @var string
     */
    private $modelAccessKey;
    /**
     * @var string
     */
    private $description;

    /**
     * @param string $tableName
     * @param string $title
     * @param string $modelAccessKey
     * @param string|null $description
     */
    public function setUp(string $tableName, string $title, string $modelAccessKey, string $description = null) {
        $this->title = $title;
        $this->modelAccessKey = $modelAccessKey;
        $this->description = $description;
    }

    /**
     * @inheritDoc
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param AbstractModelSingle $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter)($model->{$this->modelAccessKey});
    }
}
