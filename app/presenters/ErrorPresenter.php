<?php

use Nette\Application\BadRequestException;
use Nette\Diagnostics\Debugger;

/**
 * Error presenter.
 */
class ErrorPresenter extends BasePresenter {

    public function getSelectedContestSymbol() {
        return 'error';
    }

    protected function putIntoBreadcrumbs() {
        /* empty */
    }

    public function titleDefault($exception) {
        $title = _('Chyba');
        if ($exception instanceof BadRequestException) {
            $title .= ' ' . $exception->getCode();
        }
        $this->setTitle($title);
    }

    /**
     * @param  Exception
     * @return void
     */
    public function renderDefault($exception) {
        if ($this->isAjax()) { // AJAX request? Just note this error in payload.
            $this->payload->error = TRUE;
            $this->terminate();
        } elseif ($exception instanceof BadRequestException) {
            $code = $exception->getCode();
            // known exception or general 500
            $this->setView(in_array($code, array(403, 404, 405)) ? $code : '500');
            // log to access.log
            Debugger::log("HTTP code $code: {$exception->getMessage()} in {$exception->getFile()}:{$exception->getLine()}", 'access');
        } else {
            $this->setView('500'); // load template 500.latte
            Debugger::log($exception, Debugger::ERROR); // and log exception
        }
    }

}
