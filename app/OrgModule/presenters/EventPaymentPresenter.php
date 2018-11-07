<?php


namespace OrgModule;


use FKSDB\Components\Grids\EventPaymentGrid;
use Nette\NotImplementedException;

class EventPaymentPresenter extends EntityPresenter {
    /**
     * @var \ServiceEventPayment
     */
    private $serviceEventPayment;

    /**
     * @var integer
     * @persistent
     */
    public $eventId;

    public function injectServiceEventPayment(\ServiceEventPayment $serviceEventPayment) {
        $this->serviceEventPayment = $serviceEventPayment;
    }

    protected function createComponentCreateComponent($name) {
        throw new NotImplementedException('use public GUI');
    }

    protected function createComponentGrid($name) {
        return new EventPaymentGrid($this->serviceEventPayment, $this->eventId);
    }

    protected function createComponentEditComponent($name) {

    }

    protected function loadModel($id) {

    }
}
