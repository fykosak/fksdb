<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\DatabaseReflection\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Localization\ITranslator;
use Nette\Utils\Html;
use Tracy\Debugger;

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
     * @var array
     */
    private $metaData;
    /**
     * @var MetaDataFactory
     */
    private $metaDataFactory;

    /**
     * StringRow constructor.
     * @param ITranslator $translator
     * @param MetaDataFactory $metaDataFactory
     */
    public function __construct(ITranslator $translator, MetaDataFactory $metaDataFactory) {
        parent::__construct($translator);
        $this->metaDataFactory = $metaDataFactory;
    }

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
        $this->metaData = $this->metaDataFactory->getMetaData($tableName, $modelAccessKey);
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
