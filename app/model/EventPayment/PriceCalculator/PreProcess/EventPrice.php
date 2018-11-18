<?php

namespace FKSDB\EventPayment\PriceCalculator\PreProcess;

use FKSDB\EventPayment\PriceCalculator\Price;
use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelEventParticipant;
use Nette\NotImplementedException;

class EventPrice extends AbstractPreProcess {
    /**
     * @var \ServiceEventParticipant
     */
    private $serviceEventParticipant;

    public function __construct(\ServiceEventParticipant $serviceEventParticipant, $currency) {
        parent::__construct($currency);
        $this->serviceEventParticipant = $serviceEventParticipant;
    }

    public function calculate(array $data, ModelEvent $event) {
        $ids = $this->getData($data);
        foreach ($ids as $id) {
            $row = $this->serviceEventParticipant->findByPrimary($id);
            $model = ModelEventParticipant::createFromTableRow($row);
            $this->price->add($this->getPriceFromModel($model));
        }
    }

    protected function getData(array $data) {
        return $data['event_participants'];
    }

    public function getGridItems(array $data, ModelEvent $event): array {
        $items = [];
        $ids = $this->getData($data);
        foreach ($ids as $id) {
            $row = $this->serviceEventParticipant->findByPrimary($id);
            $model = ModelEventParticipant::createFromTableRow($row);
            $items[] = [
                'price' => $this->getPriceFromModel($model),
                'label' => '',// TODO
            ];
        }
        return $items;
    }

    private function getPriceFromModel(ModelEventParticipant $modelEventAccommodation): Price {
        switch ($this->price->getCurrency()) {
            case Price::CURRENCY_KC:
                $amount = $modelEventAccommodation->price;
                break;
            default:
                throw new NotImplementedException(\sprintf(_('Mena %s nieje implentovaná'), $this->price->getCurrency()));
        }
        return new Price($amount, $this->price->getCurrency());
    }
}
