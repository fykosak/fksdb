<?php

use Nette\Application\UI\Presenter;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Presenter {

    /** @var YearCalculator  */
    protected $yearCalculator;

    public function injectYearCalculator(YearCalculator $yearCalculator) {
        $this->yearCalculator = $yearCalculator;
    }

    public function getYearCalculator() {
        return $this->yearCalculator;
    }

}
