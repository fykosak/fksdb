<?php

namespace FKSDB\Components\Grids\Payment;

use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServicePayment;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 *
 * @author Mišo <miso@fykos.cz>
 */
class OrgPaymentGrid extends PaymentGrid {
    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * OrgPaymentGrid constructor.
     * @param ServicePayment $servicePayment
     * @param ModelEvent $event
     * @param TableReflectionFactory $tableReflectionFactory
     */
    public function __construct(ServicePayment $servicePayment, ModelEvent $event, TableReflectionFactory $tableReflectionFactory) {
        parent::__construct($servicePayment, $tableReflectionFactory);
        $this->event = $event;
    }

    /**
     * @param $presenter
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $schools = $this->servicePayment->getTable()->where('event_id', $this->event->event_id);

        $dataSource = new NDataSource($schools);
        $this->setDataSource($dataSource);

        $this->addColumnPaymentId();

        $this->addReflectionColumn('referenced', 'person_name', ModelPayment::class);

        $this->addColumn('person_email', _('e-mail'))->setRenderer(function ($row) {
            return ModelPerson::createFromActiveRow($row->person)->getInfo()->email;
        });

        $this->addColumnPrice();

        $this->addColumnsSymbols();

        $this->addColumnState();

        $this->addButtonDetail();
        $this->paginate = false;
    }
}
