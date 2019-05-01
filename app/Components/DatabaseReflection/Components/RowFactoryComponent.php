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
class RowFactoryComponent extends Control {
    const LAYOUT_LIST_ITEM = 'list-item';
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
     * @deprecated
     */
    private $fieldName;
    /**
     * @var int
     */
    private $userPermission;

    /**
     * StalkingRowComponent constructor.
     * @param ITranslator $translator
     * @param AbstractRow $factory
     * @param int $userPermission
     */
    public function __construct(ITranslator $translator, AbstractRow $factory, int $userPermission) {
        parent::__construct();
        $this->translator = $translator;
        $this->factory = $factory;
        $this->userPermission = $userPermission;
    }

    /**
     * @param AbstractModelSingle $model
     * @param bool $tested
     */
    public function render(AbstractModelSingle $model, bool $tested = false) {
        $this->template->setTranslator($this->translator);
        $this->template->title = $this->factory->getTitle();
        $this->template->description = $this->factory->getDescription();
        $this->template->testLog = null;
        if ($this->factory instanceof ITestedRowFactory && $tested) {
            $this->template->testLog = $this->factory->runTest($model);
        }
        $this->template->html = $this->factory->renderValue($model, $this->userPermission);
        $this->template->setFile(__DIR__ . '/layout.latte');
        $this->template->render();
    }

    /**
     * @param AbstractModelSingle $model
     * @param bool $tested
     */
    public function renderRow(AbstractModelSingle $model, bool $tested = false) {
        $this->template->layout = self::LAYOUT_ROW;
        $this->render($model, $tested);
    }

    /**
     * @param AbstractModelSingle $model
     * @param bool $tested
     */
    public function renderListItem(AbstractModelSingle $model, bool $tested = false) {
        $this->template->layout = self::LAYOUT_LIST_ITEM;
        $this->render($model, $tested);
    }

    /**
     * @param AbstractModelSingle $model
     * @param bool $tested
     */
    public function renderOnlyValue(AbstractModelSingle $model, bool $tested = false) {
        $this->template->layout = self::LAYOUT_ONLY_VALUE;
        $this->render($model, $tested);
    }
}
