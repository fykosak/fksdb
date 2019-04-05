<?php

namespace FKSDB\Components\Controls\Helpers\ValuePrinters;

use FKSDB\Components\Controls\Helpers\Badges\NotSetBadge;
use FKSDB\Components\Controls\Helpers\Badges\PermissionDeniedBadge;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;
use Nette\Utils\Html;

/**
 * Class AbstractValue
 * @package FKSDB\Components\Controls\DetailHelpers
 * @property FileTemplate $template
 */
abstract class AbstractValue extends Control {
    const LAYOUT_STALKING = 'stalking';
    /**
     * @var ITranslator
     */
    private $translator;
    /**
     * @var string|"stalking"|"normal"
     */
    private $mode;

    /**
     * AbstractValue constructor.
     * @param ITranslator $translator
     * @param string $mode
     */
    public function __construct(ITranslator $translator, string $mode = 'normal') {
        parent::__construct();
        $this->translator = $translator;
        $this->mode = $mode;
    }

    /**
     * @param string $title
     * @param bool $hasPermissions
     */
    protected function beforeRender(string $title, bool $hasPermissions) {
        $this->template->setTranslator($this->translator);
        $this->template->title = $title;
        $this->template->mode = $this->mode;
        $this->template->hasPermissions = $hasPermissions;
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
}
