<?php

namespace FKSDB\Components\Grids\Payment;

use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelPerson;
use NiftyGrid\DataSource\NDataSource;

/**
 *
 * @author Mišo <miso@fykos.cz>
 */
class OrgPaymentGrid extends PaymentGrid {
    /**
     * @var \FKSDB\ORM\Models\ModelEvent
     */
    private $event;

    /**
     * OrgPaymentGrid constructor.
     * @param \ServicePayment $servicePayment
     * @param \FKSDB\ORM\Models\ModelEvent $event
     */
    public function __construct(\ServicePayment $servicePayment, ModelEvent $event) {
        parent::__construct($servicePayment);
        $this->event = $event;
    }

    /**
     * @param $presenter
     * @throws \NiftyGrid\DuplicateButtonException
     * @throws \NiftyGrid\DuplicateColumnException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $schools = $this->servicePayment->getTable()->where('event_id', $this->event->event_id);

        $dataSource = new NDataSource($schools);
        $this->setDataSource($dataSource);

        $this->addColumnPaymentId();

        $this->addColumn('person_name', _('Person'))->setRenderer(function ($row) {
            return ModelPerson::createFromTableRow($row->person)->getFullName();
        });

        $this->addColumn('person_email', _('e-mail'))->setRenderer(function ($row) {
            return ModelPerson::createFromTableRow($row->person)->getInfo()->email;
        });

        $this->addColumnPrice();

        $this->addColumnsSymbols();

        $this->addColumnState();

        $this->addButtonDetail();
        $this->paginate = false;
    }
}
