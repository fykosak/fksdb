<?php

namespace FKSDB\Components\Controls\Schedule;

use FKSDB\Components\DatabaseReflection\ValuePrinterComponent;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\ORM\Models\Schedule\ModelScheduleGroup;
use Nette\Application\UI\Control;
use Nette\ComponentModel\IComponent;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;

/**
 * Class DetailControl
 * @package FKSDB\Components\Forms\Controls\Payment
 * @property FileTemplate $template
 */
class GroupControl extends Control {
    /**
     * @var ModelScheduleGroup
     */
    private $model;
    /**
     * @var ITranslator
     */
    private $translator;
    /**
     * @var TableReflectionFactory
     */
    private $tableReflectionFactory;

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
     * @param ModelScheduleGroup $group
     */
    public function setGroup(ModelScheduleGroup $group) {
        $this->model = $group;
    }

    public function render() {
        $this->template->model = $this->model;
        $this->template->setTranslator($this->translator);
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'GroupControl.latte');
        $this->template->render();
    }

    /**
     * @return ValuePrinterComponent
     * @throws \Exception
     */
    public function createComponentValuePrinter(): ValuePrinterComponent {
        return new ValuePrinterComponent($this->translator, $this->tableReflectionFactory);
    }
}
