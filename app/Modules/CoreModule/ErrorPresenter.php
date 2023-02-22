<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

use Fykosak\Utils\UI\PageTitle;
use FKSDB\Modules\Core\BasePresenter;
use Nette\Application\BadRequestException;
use Nette\Http\IResponse;
use Tracy\Debugger;

class ErrorPresenter extends BasePresenter
{

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Error'));
    }

    final public function renderDefault(?\Throwable $exception): void
    {
        if ($this->isAjax()) { // AJAX request? Just note this error in payload.
            $this->payload->error = true;
            $this->terminate();
        } elseif ($exception instanceof BadRequestException) {
            $code = $exception->getCode();
            // known exception or general 500
            $this->setView(
                in_array(
                    $code,
                    [
                        IResponse::S400_BadRequest,
                        IResponse::S403_Forbidden,
                        IResponse::S404_NotFound,
                        IResponse::S405_MethodNotAllowed,
                        IResponse::S410_Gone,
                    ]
                ) ? (string)$code : '500'
            );
            // log to access.log
            Debugger::log(
                "HTTP code $code: {$exception->getMessage()} in {$exception->getFile()}:{$exception->getLine()}",
                'access'
            );
        } else {
            $this->setView('500'); // load template 500.latte
            Debugger::log($exception, Debugger::ERROR); // and log exception
        }
    }

    protected function beforeRender(): void
    {
        $this->getPageStyleContainer()->styleIds[] = 'error';
        $this->getPageStyleContainer()->setNavBarClassName('bg-error navbar-dark');
        $this->getPageStyleContainer()->setNavBrandPath('/images/logo/white.svg');
        parent::beforeRender();
    }

    protected function putIntoBreadcrumbs(): void
    {
        /* empty */
    }
}
