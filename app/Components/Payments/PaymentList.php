<?php

declare(strict_types=1);

namespace FKSDB\Components\Payments;

use FKSDB\Components\Grids\Components\BaseList;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\PaymentState;
use FKSDB\Models\ORM\Services\PaymentService;
use Fykosak\NetteORM\Selection\TypedSelection;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @phpstan-extends BaseList<PaymentModel,array{
 *     state?:string,
 *     vs?:string,
 * }>
 */
final class PaymentList extends BaseList
{
    use PaymentListTrait;

    private EventModel $event;
    private PaymentService $paymentService;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container, 1024);
        $this->event = $event;
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
        $query = $this->paymentService->getTable();
        $query->where(
            ':schedule_payment.person_schedule.schedule_item.schedule_group.event_id',
            $this->event->event_id
        );
        foreach ($this->filterParams as $key => $filterParam) {
            if (!$filterParam) {
                continue;
            }
            switch ($key) {
                case 'state':
                    $query->where('state', $filterParam);
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
        $form->addSelect('state', ('State'), $states)->setPrompt(_('-- select state --'));
        $form->addText('vs', _('Variable symbol'))->setHtmlType('number');
    }

    /**
     * @throws BadTypeException
     */
    protected function configure(): void
    {
        $this->paginate = true;
        $this->filtered = true;
        $this->counter = true;
        $this->mode = self::ModePanel;
        $this->traitConfigure();
        $this->addLink('payment.detail');
    }
}
