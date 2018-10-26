<?php

namespace FKSDB\Components\Forms\Factories\Fyziklani\FyziklaniSubmit;

use Nette\Forms\Controls\RadioList;

class PointsField extends RadioList {
    public function __construct(array $availablePoints) {
        parent::__construct(_('Počet bodů'));
        $items = [];
        foreach ($availablePoints as $points) {
            $items[$points] = $points;
        }
        $this->setItems($items);
    }
}
