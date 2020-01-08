<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\DatabaseReflection\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Localization\ITranslator;
use Nette\Utils\Html;

/**
 * Class StringRow
 * @package FKSDB\Components\DatabaseReflection
 */
class StringRow extends AbstractRow {
    /**
     * @var string
     */
    protected $title;
    /**
     * @var string
     */
    protected $modelAccessKey;
    /**
     * @var string
     */
    protected $description;
    /**
     * @var array
     */
    protected $metaData;
    /**
     * @var MetaDataFactory
     */
    protected $metaDataFactory;
    /**
     * @var int
     */
    protected $permissionValue = self::PERMISSION_USE_GLOBAL_ACL;

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
     * @param $value
     */
    public function setPermissionValue(string $value) {
        $this->permissionValue = constant(self::class . '::' . $value);
    }

    /**
     * @inheritDoc
     */
    public function getPermissionsValue(): int {
        return $this->permissionValue;
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string {
        return _($this->title);
    }

    /**
     * @return string|null
     */
    public function getDescription() {
        return $this->description ? _($this->description) : '';
    }

    /**
     * @param AbstractModelSingle $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter)($model->{$this->modelAccessKey});
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        $control = new TextInput(_($this->title));
        if ($this->metaData['size']) {
            $control->addRule(Form::MAX_LENGTH, null, $this->metaData['size']);
        }

        // if (!$this->metaData['nullable']) {
        // $control->setRequired();
        //  }
        $description = $this->getDescription();
        if ($description) {
            $control->setOption('description', $this->getDescription());
        }
        return $control;
    }
}
