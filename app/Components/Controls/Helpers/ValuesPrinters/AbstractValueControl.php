<?php

namespace FKSDB\Components\Controls\Helpers\ValuePrinters;

use FKSDB\Components\Controls\Helpers\Badges\PermissionDeniedBadge;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;
use Nette\Utils\Html;

/**
 * Class AbstractValue
 * @package FKSDB\Components\Controls\DetailHelpers
 * @property FileTemplate $template
 */
abstract class AbstractValueControl extends Control {
    const LAYOUT_STALKING = 'stalking';
    const LAYOUT_NONE = 'none';
    const LAYOUT_DETAIL = 'detail';
    /**
     * @var ITranslator
     */
    private $translator;
    /**
     * @var string|"stalking"|"normal"
     */
    private $layout;
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
     * @param string $layout
     * @param string|null $title
     * @param bool $hasPermissions
     */
    public function __construct(ITranslator $translator, string $layout = self::LAYOUT_NONE, string $title = null, bool $hasPermissions = null) {
        parent::__construct();
        $this->translator = $translator;
        $this->layout = $layout;
        $this->title = $title;
        $this->hasPermissions = $hasPermissions;
    }

    /**
     * @param AbstractModelSingle $model
     * @param string $accessKey
     * @return Html
     */
    public final function createGridItem(AbstractModelSingle $model, string $accessKey): Html {
        return $this->getSafeHtml($model, $accessKey, true);
    }

    /**
     * @param string $title
     * @param bool $hasPermissions
     */
    protected function beforeRender(string $title = null, bool $hasPermissions = true) {
        $this->template->setTranslator($this->translator);
        $this->template->title = $title ?: $this->title;
        $this->template->layout = $this->layout;
        $this->template->hasPermissions = isset($this->hasPermissions) ? $this->hasPermissions : $hasPermissions;
    }

    /**
     * @param AbstractModelSingle $model
     * @param string $accessKey
     * @param bool $hasPermission
     * @return Html
     */
    protected function getSafeHtml(AbstractModelSingle $model, string $accessKey, bool $hasPermission = false): Html {
        if (!$hasPermission) {
            return PermissionDeniedBadge::getHtml();
        }
        return $this->getHtml($model, $accessKey);
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
    protected abstract function getHtml(AbstractModelSingle $model, string $accessKey): Html;

}
