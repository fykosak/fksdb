<?php

namespace FKSDB\Components\Controls\Navs;

use FKSDB\Components\Controls\BaseControl;
use FKSDB\Components\Controls\Choosers\ContestChooser;
use FKSDB\Components\Controls\Choosers\DispatchChooser;
use FKSDB\Components\Controls\Choosers\LanguageChooser;
use FKSDB\Components\Controls\Choosers\SeriesChooser;
use FKSDB\Components\Controls\Choosers\YearChooser;
use Nette\DI\Container;

/**
 * Class Nav
 * @package FKSDB\Components\Controls\Navs
 */
class Nav extends BaseControl {

    /**
     * @var string[]
     */
    private $choosers;
    /**
     * @var Container
     */
    private $context;

    /**
     * Nav constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        parent::__construct($container);
        $this->context = $container;
    }

    /**
     * @return LanguageChooser
     */
    protected function createComponentLangChooser(): LanguageChooser {
        return new LanguageChooser($this->context);
    }

    /**
     * @return DispatchChooser
     */
    protected function createComponentDispatchChooser(): DispatchChooser {
        return new DispatchChooser($this->context);
    }

    /**
     * @return YearChooser
     */
    protected function createComponentYearChooser(): YearChooser {
        return new YearChooser($this->context);
    }

    /**
     * @return SeriesChooser
     */
    protected function createComponentSeriesChooser(): SeriesChooser {
        return new SeriesChooser($this->context);
    }

    /**
     * @param string[] $choosers
     */
    public function setChoosers(array $choosers) {
        $this->choosers = $choosers;
    }

    public function render() {
        $this->template->choosers = $this->choosers;
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'Nav.latte');
        $this->template->render();
    }

    /**
     * @param object $params
     * @return object
     * redirect to correct URL
     */
    public function init($params) {
        $redirect = false;

        foreach ($this->choosers as $chooser) {
            /** @var ContestChooser $chooserControl */
            $chooserControl = $this[$chooser . 'Chooser'];
            $currentRedirect = $chooserControl->syncRedirect($params);
            $redirect = $redirect || $currentRedirect;
        }
        if ($redirect) {
            return $params;
        } else {
            return null;
        }
    }
}
