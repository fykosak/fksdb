<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

use FKSDB\Modules\Core\BasePresenter;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\BadRequestException;
use Nette\Http\IResponse;
use Tracy\Debugger;

final class ErrorPresenter extends BasePresenter
{
    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Error'), 'fas fa-triangle-exclamation');
    }

    public function authorizedDefault(): bool
    {
        return true;
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
                        IResponse::S400_BAD_REQUEST,
                        IResponse::S403_FORBIDDEN,
                        IResponse::S404_NOT_FOUND,
                        IResponse::S405_METHOD_NOT_ALLOWED,
                        IResponse::S410_GONE,
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

    protected function getStyleId(): string
    {
        return 'error';
    }
}
