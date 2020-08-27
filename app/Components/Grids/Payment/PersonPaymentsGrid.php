<?php

namespace FKSDB\Components\Grids\Payment;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 * Class PersonPaymentsGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PersonPaymentsGrid extends BaseGrid {

    protected ModelPerson $person;

    public function __construct(Container $container, ModelPerson $person) {
        parent::__construct($container);
        $this->person = $person;
    }

    protected function getData(): IDataSource {
        return new NDataSource($this->person->getPayments());
    }

    /**
     * @param Presenter $presenter
     * @return void
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws BadTypeException
     */
    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);
        $this->addColumns([
            'payment.payment_uid',
            'event.event',
            'payment.price',
            'payment.state',
            'payment.variable_symbol',
        ]);
        $this->addLink('payment.detail', false);
    }

    protected function getModelClassName(): string {
        return ModelPayment::class;
    }
}
