<?php

namespace FKSDB\Components\DatabaseReflection;

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
    const LAYOUT_LIST_GROUP = 'list-group';
    const LAYOUT_ROW = 'row';
    const LAYOUT_ONLY_VALUE = 'only-value';

    /**
     * @var callable
     */
    private $callback;
    /**
     * @var ITranslator
     */
    private $translator;
    /**
     * @var string
     */
    private $title;

    /**
     * StalkingRowComponent constructor.
     * @param ITranslator $translator
     * @param callable $callback
     * @param string $title
     */
    public function __construct(ITranslator $translator, callable $callback, string $title) {
        parent::__construct();
        $this->callback = $callback;
        $this->translator = $translator;
        $this->title = $title;
    }

    /**
     * @return string|"list-group"|"row"|"only-value"
     */
    abstract protected function getLayout(): string;

    /**
     * @param AbstractModelSingle $model
     */
    public function render(AbstractModelSingle $model) {
        $this->template->setTranslator($this->translator);
        $this->template->title = $this->title;
        $this->template->layout = $this->getLayout();
        $this->template->html = ($this->callback)($model);
        $this->template->setFile(__DIR__ . '/layout.latte');
        $this->template->render();
    }
}
