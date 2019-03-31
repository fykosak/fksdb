<?php


namespace FKSDB\Components\Controls\Stalking;

/**
 * Class Payment
 * @package FKSDB\Components\Controls\Stalking
 */
class Payment extends StalkingComponent {
    public function render() {
        $this->beforeRender();
        $this->template->payments = $this->modelPerson->getPayments();
        $this->template->setFile(__DIR__ . '/Payment.latte');
        $this->template->render();
    }
    /**
     * @return array
     */
    protected function getAllowedPermissions(): array {
        return [StalkingComponent::PERMISSION_FULL, StalkingComponent::PERMISSION_RESTRICT];
    }

    /**
     * @return string
     */
    protected function getHeadline(): string {
        return _('Payments');
    }
}
