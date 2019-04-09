<?php

namespace FKSDB\Components\Controls\Helpers\ValuePrinters;

use FKSDB\Components\Controls\Helpers\Badges\NotSetBadge;
use FKSDB\Components\Controls\Helpers\Badges\PermissionDeniedBadge;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;
use Nette\Utils\Html;
use Tracy\Debugger;

/**
 * Class AbstractValue
 * @package FKSDB\Components\Controls\DetailHelpers
 * @property FileTemplate $template
 */
abstract class AbstractValue extends Control {
    const LAYOUT_STALKING = 'stalking';
    const LAYOUT_NONE = 'none';
    /**
     * @var ITranslator
     */
    private $translator;
    /**
     * @var string|"stalking"|"normal"
     */
    private $mode;
    /**
     * @var string
     */
    protected $title;
    /**
     * @var bool
     */
    private $hasPermissions;

    /**
     * AbstractValue constructor.
     * @param ITranslator $translator
     * @param string $mode
     * @param string|null $title
     * @param bool $hasPermissions
     */
    public function __construct(ITranslator $translator, string $mode = 'normal', string $title = null, bool $hasPermissions = null) {
        parent::__construct();
        $this->translator = $translator;
        $this->mode = $mode;
        $this->title = $title;
        $this->hasPermissions = $hasPermissions;
    }

    /**
     * @param string $title
     * @param bool $hasPermissions
     */
    protected function beforeRender(string $title = null, bool $hasPermissions = true) {
        $this->template->setTranslator($this->translator);
        $this->template->title = $title ?: $this->title;
        $this->template->mode = $this->mode;
        $this->template->hasPermissions = isset($this->hasPermissions) ? $this->hasPermissions : $hasPermissions;
    }

    /**
     * @return NotSetBadge
     */
    public function createComponentNotSet(): NotSetBadge {
        return new NotSetBadge($this->translator);
    }

    /**
     * @return PermissionDeniedBadge
     */
    public function createComponentPermissionDenied(): PermissionDeniedBadge {
        return new PermissionDeniedBadge($this->translator);
    }

    /**
     * @param AbstractModelSingle $model
     * @param string $accessKey
     * @return Html
     */
    abstract public function createGridItem(AbstractModelSingle $model, string $accessKey): Html;
}
