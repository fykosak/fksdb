<?php

namespace FKSDB\Components\Controls\Navs;

use FKSDB\Components\Controls\Choosers\BrawlChooser;
use FKSDB\Components\Controls\Choosers\Chooser;
use FKSDB\Components\Controls\Choosers\DispatchChooser;
use FKSDB\Components\Controls\Choosers\LanguageChooser;
use FKSDB\Components\Controls\Choosers\SeriesChooser;
use FKSDB\Components\Controls\Choosers\YearChooser;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Diagnostics\Debugger;
use Nette\Http\Session;
use Nette\Localization\ITranslator;

class Nav extends Control {

    /**
     * @var \ServiceEvent
     */
    private $serviceEvent;

    /**
     * @var string
     */
    protected $role;
    /**
     * @var \YearCalculator
     */
    protected $yearCalculator;
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var \ServiceContest
     */
    protected $serviceContest;

    /**
     * @var \SeriesCalculator
     */
    private $seriesCalculator;

    /**
     * @var ITranslator
     */
    private $translator;

    /**
     * @var string[]
     */
    private $choosers;

    public function __construct(
        \YearCalculator $yearCalculator,
        \SeriesCalculator $seriesCalculator,
        Session $session,
        \ServiceContest $serviceContest,
        ITranslator $translator
    ) {
        parent::__construct();
        $this->yearCalculator = $yearCalculator;
        $this->session = $session;
        $this->serviceContest = $serviceContest;
        $this->translator = $translator;
        $this->seriesCalculator = $seriesCalculator;
    }

    /**
     * @return LanguageChooser
     */
    protected function createComponentLangChooser() {
        $control = new LanguageChooser($this->session);
        return $control;
    }

    /**
     * @return DispatchChooser
     */
    protected function createComponentDispatchChooser() {
        $control = new DispatchChooser($this->session, $this->yearCalculator, $this->serviceContest);

        return $control;
    }

    /**
     * @return YearChooser
     */
    protected function createComponentYearChooser() {
        $control = new YearChooser($this->session, $this->yearCalculator, $this->serviceContest);

        return $control;
    }

    /**
     * @return BrawlChooser
     */
    protected function createComponentBrawlChooser() {
        $control = new BrawlChooser($this->serviceEvent);
        return $control;
    }

    /**
     * @return SeriesChooser
     */
    protected function createComponentSeriesChooser() {
        $control = new SeriesChooser($this->session, $this->seriesCalculator, $this->serviceContest, $this->translator);

        return $control;
    }

    public function setChoosers($choosers) {
        $this->choosers = $choosers;
    }

    public function render() {
        $this->template->choosers = $this->choosers;
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'Nav.latte');
        $this->template->render();
    }

    /**
     * @param $params object
     * @return object
     * redirect to correct URL
     */
    public function init($params) {
        $redirect = false;

        foreach ($this->choosers as $chooser) {
            Debugger::barDump($chooser);
            /**
             * @var $chooserControl Chooser
             */
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
