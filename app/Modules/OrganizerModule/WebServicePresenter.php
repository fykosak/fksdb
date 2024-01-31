<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Models\WebService\SoapResponse;
use FKSDB\Modules\Core\BasePresenter;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\AbortException;

final class WebServicePresenter extends BasePresenter
{
    private \SoapServer $server;

    final public function injectSoapServer(\SoapServer $server): void
    {
        $this->server = $server;
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('SOAP'));
    }

    public function authorizedDefault(): bool
    {
        return true;
    }

    final public function renderDefault(): void
    {
        try {
            $response = new SoapResponse($this->server);
            $this->sendResponse($response);
        } catch (AbortException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            $this->redirect('Dashboard:');
        }
    }

    public function requiresLogin(): bool
    {
        return false;
    }
}
