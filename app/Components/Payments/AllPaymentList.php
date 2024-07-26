<?php

declare(strict_types=1);

namespace FKSDB\Components\Payments;

use FKSDB\Components\Grids\Components\BaseList;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\PaymentState;
use FKSDB\Models\ORM\Services\PaymentService;
use Fykosak\NetteORM\Selection\TypedSelection;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @phpstan-extends BaseList<PaymentModel,array{state:string|null,vs:string|null}>
 */
class AllPaymentList extends BaseList
{
    use PaymentListTrait;

    private PaymentService $paymentService;

    public function __construct(Container $container)
    {
        parent::__construct($container, 1024);
    }

    public function inject(PaymentService $paymentService): void
    {
        $this->paymentService = $paymentService;
    }

    /**
     * @phpstan-return TypedSelection<PaymentModel>
     */
    protected function getModels(): TypedSelection
    {
        $query = $this->paymentService->getTable()->order('payment_id DESC');
        foreach ($this->filterParams as $key => $filterParam) {
            if (!$filterParam) {
                continue;
            }
            switch ($key) {
                case 'state':
                    $query->where('payment.state', $filterParam);
                    break;
                case 'vs':
                    $query->where('variable_symbol', $filterParam);
            }
        }
        return $query;
    }

    protected function configureForm(Form $form): void
    {
        $states = [];
        foreach (PaymentState::cases() as $case) {
            $states[$case->value] = $case->label();
        }
        $form->addSelect('state', ('State'), $states)->setPrompt(_('Select state'));
        $form->addText('vs', _('Variable symbol'))->setHtmlType('number');
    }

    protected function configure(): void
    {
        $this->paginate = true;
        $this->filtered = true;
        $this->counter = true;
        $this->mode = self::ModePanel;
        $this->traitConfigure();
    }
}
