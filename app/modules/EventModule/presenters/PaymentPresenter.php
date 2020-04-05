<?php

namespace EventModule;

use FKSDB\Components\Controls\Transitions\TransitionButtonsControl;
use FKSDB\Components\Forms\Controls\Payment\SelectForm;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\Payment\OrgPaymentGrid;
use FKSDB\Config\Extensions\PaymentExtension;
use FKSDB\NotImplementedException;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Services\ServicePayment;
use FKSDB\Payment\Transition\PaymentMachine;
use FKSDB\Transitions\Machine;
use InvalidArgumentException;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use function count;
use function sprintf;

/**
 * Class PaymentPresenter
 * @package EventModule
 * @method ModelPayment getEntity
 * @method ModelPayment loadEntity(int $id)
 */
class PaymentPresenter extends BasePresenter {
    use EventEntityTrait;
    /**
     * @var Machine
     */
    private $machine;

    /**
     * @var ServicePayment
     */
    private $servicePayment;


    /**
     * @param ServicePayment $servicePayment
     */
    public function injectServicePayment(ServicePayment $servicePayment) {
        $this->servicePayment = $servicePayment;
    }

    /* ********* titles *****************/
    public function titleCreate() {
        $this->setTitle(_('New payment'), 'fa fa-credit-card');
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function titleEdit(int $id) {
        $this->setTitle(sprintf(_('Edit payment #%s'), $this->loadEntity($id)->getPaymentId()), 'fa fa-credit-card');
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function titleDetail(int $id) {
        $this->setTitle(sprintf(_('Payment detail #%s'), $this->loadEntity($id)->getPaymentId()), 'fa fa-credit-card');
    }

    public function titleList() {
        $this->setTitle(_('List of payments'), 'fa fa-credit-card');
    }

    /**
     * @return bool
     */
    protected function isEnabled(): bool {
        return $this->hasApi();
    }
    /* ********* Authorization *****************/

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function authorizedEdit(int $id) {
        $this->loadEntity($id);
        $this->setAuthorized($this->canEdit());
    }

    /**
     * @param $resource
     * @param string $privilege
     * @return bool
     * @throws BadRequestException
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return $this->isContestsOrgAuthorized($resource, $privilege);
    }

    /* ********* actions *****************/
    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function actionDetail(int $id) {
        $this->loadEntity($id);
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function actionEdit(int $id) {
        $this->loadEntity($id);
        if (!$this->canEdit()) {
            $this->flashMessage(sprintf(_('Payment #%s can not be edited'), $this->getEntity()->getPaymentId()), \BasePresenter::FLASH_ERROR);
            $this->redirect(':MyPayments:');
        }
        /**
         * @var SelectForm $component
         */
        $component = $this->getComponent('form');
        $component->setModel($this->getEntity());
    }

    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    public function actionCreate() {
        if ((count($this->getMachine()->getAvailableTransitions(null)) === 0)) {
            $this->flashMessage(_('Payment is not allowed in this time!'));
            if (!$this->isOrg()) {
                $this->redirect('Dashboard:default');
            }
        }
    }

    /* ********* render *****************/
    public function renderEdit() {
        $this->template->model = $this->getEntity();
    }

    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    public function renderDetail() {
        $this->template->items = $this->getMachine()->getPriceCalculator()->getGridItems($this->getEntity());
        $this->template->model = $this->getEntity();
        $this->template->isOrg = $this->isOrg();
    }
    /* ********* startup *****************/
    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function startup() {
        parent::startup();
    }
    /* ********* Components *****************/
    /**
     * @return OrgPaymentGrid
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function createComponentOrgGrid(): OrgPaymentGrid {
        return new OrgPaymentGrid($this->getEvent(), $this->getContext());
    }

    /**
     * @return SelectForm
     * @throws BadRequestException
     * @throws AbortException
     */
    protected function createComponentForm(): SelectForm {
        return new SelectForm(
            $this->getContext(),
            $this->getEvent(),
            $this->isOrg(),
            ['accommodation'],
            $this->getMachine()
        );
    }

    /**
     * @return TransitionButtonsControl
     */
    protected function createComponentTransitionButtons(): TransitionButtonsControl {
        return $this->machine->createComponentTransitionButtons($this->getEntity());
    }


    /**
     * Is org or (is own payment and can edit)
     * @return bool
     * @throws BadRequestException
     */
    private function canEdit(): bool {
        return ($this->getEntity()->canEdit() && $this->isContestsOrgAuthorized($this->getEntity(), 'edit')) ||
            $this->isOrg();
    }

    /**
     * @return bool
     * @throws BadRequestException
     */
    private function isOrg(): bool {
        return $this->isContestsOrgAuthorized($this->getModelResource(), 'org');
    }

    /**
     * @return PaymentMachine
     * @throws AbortException
     * @throws BadRequestException
     * @throws \Exception
     */
    private function getMachine(): PaymentMachine {
        if (!$this->machine) {
            $this->machine = $this->getContext()->getService('payment.' . PaymentExtension::MACHINE_PREFIX . $this->getEvent()->event_id);
            if (!$this->machine instanceof PaymentMachine) {
                throw new BadRequestException();
            }
        }
        if (!$this->machine instanceof PaymentMachine) {
            throw new InvalidArgumentException(_('Expected class PaymentMachine'), 500);
        }
        return $this->machine;
    }

    /**
     * @return bool
     */
    private function hasApi(): bool {
        try {
            $this->getMachine();
        } catch (\Exception $exception) {
            return false;
        }
        return true;
    }

    /**
     * @return ServicePayment
     */
    function getORMService() {
        return $this->servicePayment;
    }

    /**
     * @inheritDoc
     */
    public function createComponentGrid(): BaseGrid {
        throw new NotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function createComponentCreateForm(): Control {
        throw new NotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function createComponentEditForm(): Control {
        throw new NotImplementedException();
    }
}
