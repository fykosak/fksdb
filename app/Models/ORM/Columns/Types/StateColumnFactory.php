<?php

namespace FKSDB\Models\ORM\Columns\Types;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use Nette\Forms\Controls\SelectBox;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use Nette\Forms\Controls\SelectBox;
use Nette\Utils\Html;

/**
 * Class StateRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class StateColumnFactory extends ColumnFactory {

    protected array $states = [];

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        $state = $model->{$this->getModelAccessKey()};
        if (is_null($state)) {
            return NotSetBadge::getHtml();
        }
        $stateDef = $this->getState($state);
        return Html::el('span')->addAttributes(['class' => $stateDef['badge']])->addText(_($stateDef['label']));
    }

    public function setStates(array $states): void {
        $this->states = $states;
    }

    public function getState(string $state): array {
        if (isset($this->states[$state])) {
            return $this->states[$state];
        }
        return ['badge' => '', 'label' => ''];
    }

    protected function createFormControl(...$args): SelectBox {
        return new SelectBox($this->getTitle(), $this->getItems());
    }

    protected function getItems(): array {
        $data = [];
        foreach ($this->states as $key => $state) {
            $data[$key] = $state['label'];
        }
        return $data;
    }
}
