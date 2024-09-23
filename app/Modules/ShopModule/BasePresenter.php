<?php

declare(strict_types=1);

namespace FKSDB\Modules\ShopModule;

use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\PaymentState;
use FKSDB\Models\Transitions\Machine\PaymentMachine;
use FKSDB\Models\Transitions\TransitionsMachineFactory;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Fykosak\Utils\UI\Title;
use Nette\Application\UI\Template;

abstract class BasePresenter extends \FKSDB\Modules\Core\BasePresenter
{
    protected const AvailableEventIds = [182]; //phpcs:ignore

    protected TransitionsMachineFactory $machineFactory;

    public function injectMachineFactory(TransitionsMachineFactory $machineFactory): void
    {
        $this->machineFactory = $machineFactory;
    }

    final protected function getMachine(): PaymentMachine
    {
        static $machine;
        if (!isset($machine)) {
            $machine = $this->machineFactory->getPaymentMachine();
        }
        return $machine;
    }

    public function getContest(): ContestModel
    {
        /** @var ContestModel $contest */
        $contest = $this->contestService->findByPrimary(ContestModel::ID_FYKOS);
        return $contest;
    }

    protected function createTemplate(): Template
    {
        $template = parent::createTemplate();
        $template->payments = $this->getInProgressPayments();
        return $template;
    }

    /**
     * @return TypedGroupedSelection<PaymentModel>
     */
    public function getInProgressPayments(): TypedGroupedSelection
    {
        static $payments;
        if (!isset($payments)) {
            $person = $this->getLoggedPerson();
            $payments = $person->getPayments()->where('state', PaymentState::IN_PROGRESS);
        }
        return $payments;
    }

    protected function getNavRoots(): array
    {
        return [
            [
                'title' => new Title(null, _('Shop & payments')),
                'items' => [
                    'Shop:Home:default' => [],
                ],
            ],
        ];
    }
}
