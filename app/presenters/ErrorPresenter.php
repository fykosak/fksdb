<?php

use Nette\Application\BadRequestException;
use Nette\Diagnostics\Debugger;

/**
 * Error presenter.
 */
class ErrorPresenter extends BasePresenter {

    public function getNavBarVariant(): array {
        return ['error', 'bg-error navbar-dark'];
    }

    protected function putIntoBreadcrumbs() {
        /* empty */
    }

    public function titleDefault() {
        $this->setTitle(_('Chyba'));
    }

    /**
     * @param  Exception
     * @return void
     * @throws \Nette\Application\AbortException
     */
    public function renderDefault($exception) {
        if ($this->isAjax()) { // AJAX request? Just note this error in payload.
            $this->payload->error = TRUE;
            $this->terminate();
        } elseif ($exception instanceof BadRequestException) {
            $code = $exception->getCode();
            // known exception or general 500
            $this->setView(in_array($code, [403, 404, 405]) ? $code : '500');
            // log to access.log
            Debugger::log("HTTP code $code: {$exception->getMessage()} in {$exception->getFile()}:{$exception->getLine()}", 'access');
        } else {
            $this->setView('500'); // load template 500.latte
            Debugger::log($exception, Debugger::ERROR); // and log exception
        }
    }
}
