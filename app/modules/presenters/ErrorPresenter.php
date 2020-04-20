<?php

use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Http\Response;
use Tracy\Debugger;

/**
 * Error presenter.
 */
class ErrorPresenter extends BasePresenter {

    /**
     * @return array
     */
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
     * @param Exception
     * @return void
     * @throws AbortException
     */
    public function renderDefault($exception) {
        if ($this->isAjax()) { // AJAX request? Just note this error in payload.
            $this->payload->error = TRUE;
            $this->terminate();
        } elseif ($exception instanceof BadRequestException) {
            $code = $exception->getCode();
            // known exception or general 500
            $this->setView(in_array($code, [Response::S403_FORBIDDEN, Response::S404_NOT_FOUND, Response::S405_METHOD_NOT_ALLOWED, Response::S410_GONE]) ? $code : '500');
            // log to access.log
            Debugger::log("HTTP code $code: {$exception->getMessage()} in {$exception->getFile()}:{$exception->getLine()}", 'access');
        } else {
            $this->setView('500'); // load template 500.latte
            Debugger::log($exception, Debugger::ERROR); // and log exception
        }
    }
}
