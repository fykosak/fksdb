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

    public function __construct(\ServiceEventParticipant $serviceEventParticipant) {
        $this->serviceEventParticipant = $serviceEventParticipant;
    }

    public function calculate(array $data, ModelEvent $event, $currency): Price {
        $price = new Price(0, $currency);
        $ids = $this->getData($data);
        foreach ($ids as $id) {
            $row = $this->serviceEventParticipant->findByPrimary($id);
            $model = ModelEventParticipant::createFromTableRow($row);
            $price->add($this->getPriceFromModel($model, $price));
        }
        return $price;
    }

    protected function getData(array $data) {
        return $data['event_participants'];
    }

    public function getGridItems(array $data, ModelEvent $event, $currency): array {
        $price = new Price(0, $currency);
        $items = [];
        $ids = $this->getData($data);
        foreach ($ids as $id) {
            $row = $this->serviceEventParticipant->findByPrimary($id);
            $model = ModelEventParticipant::createFromTableRow($row);
            $items[] = [
                'price' => $this->getPriceFromModel($model, $price),
                'label' => '',// TODO
            ];
        }
        return $items;
    }

    private function getPriceFromModel(ModelEventParticipant $modelEventAccommodation, Price $price): Price {
        switch ($price->getCurrency()) {
            case Price::CURRENCY_KC:
                $amount = $modelEventAccommodation->price;
                break;
            default:
                throw new NotImplementedException(\sprintf(_('Mena %s nieje implentovaná'), $price->getCurrency()));
        }
        return new Price($amount, $price->getCurrency());
    }
}
