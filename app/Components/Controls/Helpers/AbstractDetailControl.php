<?php

namespace FKSDB\Components\Controls\Helpers;

use FKSDB\Components\Controls\Helpers;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;

/**
 * Class DetailControl
 * @package FKSDB\Components\Controls\Stalking\Helpers
 */
abstract class AbstractDetailControl extends Control {

    /**
     * @var TableReflectionFactory
     */
    protected $tableReflectionFactory;

    /**
     * @var ITranslator
     */
    protected $translator;

    /**
     * DetailControl constructor.
     * @param ITranslator $translator
     * @param TableReflectionFactory $tableReflectionFactory
     */
    public function __construct(ITranslator $translator, TableReflectionFactory $tableReflectionFactory) {
        parent::__construct();
        $this->translator = $translator;
        $this->tableReflectionFactory=$tableReflectionFactory;
    }

    /**
     * @return Helpers\ValuePrinters\PhoneValueControl
     */
    public function createComponentPhoneValue(): Helpers\ValuePrinters\PhoneValueControl {
        return new Helpers\ValuePrinters\PhoneValueControl($this->translator);
    }

    /**
     * @return Helpers\ValuePrinters\IsSetValueControl
     */
    public function createComponentIsSetValue(): Helpers\ValuePrinters\IsSetValueControl {
        return new Helpers\ValuePrinters\IsSetValueControl($this->translator);
    }

    /**
     * @return Helpers\ValuePrinters\BinaryValueControl
     */
    public function createComponentBinaryValue(): Helpers\ValuePrinters\BinaryValueControl {
        return new Helpers\ValuePrinters\BinaryValueControl($this->translator);
    }

    /**
     * @return Helpers\ValuePrinters\StringValueControl
     */
    public function createComponentStringValue(): Helpers\ValuePrinters\StringValueControl {
        return new Helpers\ValuePrinters\StringValueControl($this->translator);
    }


    /**
     * @param string $name
     * @return \Nette\ComponentModel\IComponent|null
     * @throws \Exception
     */
    public function createComponent($name) {
        $parts = \explode('__', $name);
        if (\count($parts) === 3) {
            list($prefix, $tableName, $fieldName) = $parts;
            if ($prefix === 'valuePrinter' || $prefix === 'valuePrinterDetail'||$prefix === 'valuePrinterRow') {
                return $this->tableReflectionFactory->createRowComponent($tableName, $fieldName, 2048);
            }
            if ($prefix === 'valuePrinterStalking'||$prefix == 'valuePrinterList') {
                return $this->tableReflectionFactory->createListComponent($tableName, $fieldName, 2048);
            }
            if($prefix === 'valuePrinterOnlyValue'){
                return $this->tableReflectionFactory->createOnlyValueComponent($tableName, $fieldName, 2048);
            }
        }

        return parent::createComponent($name);
    }

}
