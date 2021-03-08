<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Models\Authorization\ContestAuthorizator;
use FKSDB\Modules\Core\BasePresenter;
use Nette\Application\AbortException;
use Tracy\Debugger;
use FKSDB\Models\WebService\SoapResponse;

/**
 * Description of WebServicePresenter
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class WebServicePresenter extends BasePresenter {

    private \SoapServer $server;
    private ContestAuthorizator $contestAuthorizator;

    final public function injectSoapServer(\SoapServer $server, ContestAuthorizator $contestAuthorizator): void {
        $this->server = $server;
        $this->contestAuthorizator = $contestAuthorizator;
    }

    /* TODO */
    public function authorizedDefault(): void {
        $this->setAuthorized($this->contestAuthorizator->isAllowedForAnyContest('webService', 'default'));
    }

    /**
     * @throws AbortException
     */
    public function renderDefault(): void {
        try {
            $response = new SoapResponse($this->server);
            $this->sendResponse($response);
        } catch (AbortException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            Debugger::log($exception);
            $this->redirect('Dashboard:');
        }
    }
}
