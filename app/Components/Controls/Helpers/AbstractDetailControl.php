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
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    /**
     * @return Helpers\ValuePrinters\StringValueControl
     */
    public function createComponentStringValue(): Helpers\ValuePrinters\StringValueControl {
        return new Helpers\ValuePrinters\StringValueControl($this->translator);
    }

    /**
     * @param string $name
     * @return \FKSDB\Components\DatabaseReflection\AbstractRowComponent|\Nette\ComponentModel\IComponent|null
     * @throws \Exception
     */
    public function createComponent($name) {
        $printerComponent = $this->tableReflectionFactory->createComponent($name, 2048);
        if ($printerComponent) {
            return $printerComponent;
        }
        return parent::createComponent($name);
    }


}
