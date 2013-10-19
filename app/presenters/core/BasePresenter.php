<?php

use FKS\Components\Controls\JavaScriptLoader;
use FKS\Components\Controls\StylesheetLoader;
use FKS\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKS\Components\Forms\Controls\Autocomplete\IJSONProvider;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\Presenter;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Presenter implements IJavaScriptCollector, IStylesheetCollector, IJSONProvider {

    /** @var YearCalculator  */
    protected $yearCalculator;

    /** @var ServiceContest */
    protected $serviceContest;

    public function getYearCalculator() {
        return $this->yearCalculator;
    }

    public function injectYearCalculator(YearCalculator $yearCalculator) {
        $this->yearCalculator = $yearCalculator;
    }

    public function getServiceContest() {
        return $this->serviceContest;
    }

    public function injectServiceContest(ServiceContest $serviceContest) {
        $this->serviceContest = $serviceContest;
    }

    public function handleAutocomplete($acName, $acQ) {
        if (!$this->isAjax()) {
            throw new BadRequestException('Can be called only by AJAX.');
        }
        $component = $this->getComponent($acName);
        if (!($component instanceof AutocompleteSelectBox)) {
            throw new InvalidArgumentException('Cannot handle component of type ' . get_class($component) . '.');
        } else {
            $data = $component->getDataProvider()->getFilteredItems($acQ);
            $response = new JsonResponse($data);
            $this->sendResponse($response);
        }
    }

    protected function createComponentJsLoader($name) {
        $component = new JavaScriptLoader();
        return $component;
    }

    protected function createComponentCssLoader($name) {
        $component = new StylesheetLoader();
        return $component;
    }

    /*     * ******************************
     * IJavaScriptCollector
     * ****************************** */

    public function registerJSFile($file) {
        $this['jsLoader']->addFile($file);
    }

    public function registerJSCode($code) {
        $this['jsLoader']->addInline($code);
    }

    /*     * ******************************
     * IStylesheetCollector
     * ****************************** */

    public function registerStylesheetFile($file, $media = array()) {
        $this['cssLoader']->addFile($file, $media);
    }

}
