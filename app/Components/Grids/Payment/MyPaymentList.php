<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Payment;

use FKSDB\Components\Grids\Components\BaseList;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Nette\DI\Container;

/**
 * @phpstan-extends BaseList<PaymentModel,array{
 *     state?:string,
 *     vs?:string,
 * }>
 */
final class MyPaymentList extends BaseList
{
    private PersonModel $person;
    use PaymentListTrait;

    public function __construct(Container $container, PersonModel $person)
    {
        parent::__construct($container, 1024);
        $this->person = $person;
    }

    /**
     * @phpstan-return TypedGroupedSelection<PaymentModel>
     */
    protected function getModels(): TypedGroupedSelection
    {
        return $this->person->getPayments();
    }

    protected function configure(): void
    {
        $this->paginate = true;
        $this->filtered = false;
        $this->counter = true;
        $this->mode = self::ModePanel;
        $this->traitConfigure();
    }
}
