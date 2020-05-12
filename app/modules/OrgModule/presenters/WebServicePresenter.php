<?php

namespace OrgModule;

use BasePresenter;
use Nette\Application\AbortException;
use Tracy\Debugger;
use FKSDB\WebService\SoapResponse;

/**
 * Description of WebServicePresenter
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class WebServicePresenter extends BasePresenter {
    /**
     * @throws AbortException
     */
    public function renderDefault() {
        /** @var \SoapServer $server */
        $server = $this->getContext()->getByType(\SoapServer::class);
        try {
            $response = new SoapResponse($server);
            $this->sendResponse($response);
        } catch (AbortException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            Debugger::log($exception);
            $this->redirect('Dashboard:');
        }
    }

}
