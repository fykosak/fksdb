<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\Choosers\LanguageChooser;
use FKSDB\Modules\Core\BasePresenter;
use Nette\Application\AbortException;
use Tracy\Debugger;
use FKSDB\WebService\SoapResponse;

/**
 * Description of WebServicePresenter
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class WebServicePresenter extends BasePresenter {

    private \SoapServer $server;

    public function injectSoapServer(\SoapServer $server): void {
        $this->server = $server;
    }

    protected function startupLangChooser(): void {
        /** @var LanguageChooser $control */
        $control = $this->getComponent('languageChooser');
        $control->init(false);
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
