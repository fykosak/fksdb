<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\Forms\Factories\ITestedRowFactory;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;

/**
 * Class AbstractRowComponent
 * @package FKSDB\Components\DatabaseReflection
 * @property FileTemplate $template
 */
abstract class AbstractRowComponent extends Control {
    const LAYOUT_LIST_GROUP = 'list-item';
    const LAYOUT_ROW = 'row';
    const LAYOUT_ONLY_VALUE = 'only-value';
    /**
     * @var ITranslator
     */
    private $translator;
    /**
     * @var AbstractRow
     */
    private $factory;
    /**
     * @var string
     */
    private $fieldName;
    /**
     * @var int
     */
    private $userPermission;
    /**
     * @var bool
     */
    protected $includeTest = false;

    /**
     * StalkingRowComponent constructor.
     * @param ITranslator $translator
     * @param AbstractRow $factory
     * @param string $fieldName
     * @param int $userPermission
     */
    public function __construct(ITranslator $translator, AbstractRow $factory, string $fieldName, int $userPermission) {
        parent::__construct();
        $this->translator = $translator;
        $this->factory = $factory;
        $this->fieldName = $fieldName;
        $this->userPermission = $userPermission;
    }

    /**
     * @return string|"list-item"|"row"|"only-value"
     */
    abstract protected function getLayout(): string;

    /**
     * @param AbstractModelSingle $model
     */
    public function render(AbstractModelSingle $model) {
        $this->template->setTranslator($this->translator);
        $this->template->title = $this->factory->getTitle();
        $this->template->description = $this->factory->getDescription();
        $this->template->layout = $this->getLayout();
        $this->template->testLog = null;
        if ($this->factory instanceof ITestedRowFactory && $this->includeTest) {
            $this->template->testLog = $this->factory->runTest($model);
        }
        $this->template->html = $this->factory->renderValue($model, $this->fieldName, $this->userPermission);
        $this->template->setFile(__DIR__ . '/layout.latte');
        $this->template->render();
    }
}
