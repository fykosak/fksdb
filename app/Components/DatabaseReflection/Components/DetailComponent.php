<?php

namespace FKSDB\Components\DatabaseReflection;

use Exception;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use Nette\Application\UI\Control;
use Nette\ComponentModel\IComponent;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;

/**
 * Class DetailComponent
 * @package FKSDB\Components\DatabaseReflection
 * @property FileTemplate $template
 */
class DetailComponent extends Control {
    /**
     * @var TableReflectionFactory
     */
    private $tableReflectionFactory;
    /**
     * @var ITranslator
     */
    private $translator;
    /**
     * @var DetailFactory
     */
    private $detailFactory;

    /**
     * DetailComponent constructor.
     * @param DetailFactory $detailFactory
     * @param TableReflectionFactory $tableReflectionFactory
     * @param ITranslator $translator
     */
    public function __construct(DetailFactory$detailFactory,TableReflectionFactory $tableReflectionFactory, ITranslator $translator) {
        parent::__construct();
        $this->detailFactory=$detailFactory;
        $this->tableReflectionFactory = $tableReflectionFactory;
        $this->translator = $translator;
    }

    /**
     * @param string $name
     * @return IComponent|null
     * @throws Exception
     */
    public function createComponent($name) {
        $printerComponent = $this->tableReflectionFactory->createComponent($name, 2048);
        if ($printerComponent) {
            return $printerComponent;
        }
        return parent::createComponent($name);
    }

    /**
     * @param $section
     * @param $model
     */
    public function render($section,$model) {
        $this->template->setTranslator($this->translator);

     $this->template->data = $this->detailFactory->getSection($section);

        $this->template->model = $model;
        $this->template->setFile(__DIR__ . '/detail.latte');
        $this->template->render();
    }
}
