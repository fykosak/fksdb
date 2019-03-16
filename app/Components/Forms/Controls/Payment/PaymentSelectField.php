<?php


namespace FKSDB\Components\Forms\Controls\Payment;

use FKSDB\Components\React\IReactComponent;
use FKSDB\Components\React\ReactField;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventPersonAccommodation;
use Nette\Forms\Controls\TextInput;

/**
 * Class PaymentSelectField
 * @package FKSDB\Components\Forms\Controls\Payment
 */
class PaymentSelectField extends TextInput implements IReactComponent {

    use ReactField;

    /**
     * @var \ServiceEventPersonAccommodation
     */
    private $serviceEventPersonAccommodation;
    /**
     * @var \FKSDB\ORM\Models\ModelEvent
     */
    private $event;

    private $showAll = true;

    /**
     * PaymentSelectField constructor.
     * @param \ServiceEventPersonAccommodation $serviceEventPersonAccommodation
     * @param \FKSDB\ORM\Models\ModelEvent $event
     * @param bool $showAll
     */
    public function __construct(\ServiceEventPersonAccommodation $serviceEventPersonAccommodation, ModelEvent $event, bool $showAll = true) {
        parent::__construct();
        $this->serviceEventPersonAccommodation = $serviceEventPersonAccommodation;
        $this->event = $event;
        $this->showAll = $showAll;
        $this->appendProperty();
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getData(): string {
        $query = $this->serviceEventPersonAccommodation->where('event_accommodation.event_id', $this->event->event_id);
        $items = [];
        foreach ($query as $row) {
            $model = ModelEventPersonAccommodation::createFromTableRow($row);
            if ($this->showAll || !$model->related(\DbNames::TAB_PAYMENT_ACCOMMODATION, 'event_person_accommodation_id')->count()) {
                $items[] = [
                    'hasPayment' => false, /*
                    ->where('payment.state !=? OR payment.state IS NULL', ModelPayment::STATE_CANCELED)->count(),*/
                    'label' => $model->getLabel(),
                    'id' => $model->event_person_accommodation_id,
                    'accommodation' => $model->getEventAccommodation()->__toArray(),
                    'personId' => $model->person_id,
                    'personName' => $model->getPerson()->getFullName(),
                    'personFamilyName' => $model->getPerson()->family_name,
                ];
            }
        }
        return \json_encode($items);
    }

    /**
     * @return string
     */
    public function getComponentName(): string {
        return 'accommodation-select';
    }

    /**
     * @return string
     */
    public function getMode(): string {
        return '';
    }

    /**
     * @return string
     */
    public function getModuleName(): string {
        return 'payment';
    }

    /**
     * @return array
     */
    public function getActions(): array {
        return [];
    }
}
