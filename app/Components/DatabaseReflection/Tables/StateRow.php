<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\NotImplementedException;
use Nette\Utils\Html;

/**
 * Class StateRow
 * @package FKSDB\Components\DatabaseReflection
 */
class StateRow extends DefaultRow {
    /**
     * @var array[]
     */
    protected $states = [];

    /**
     * @inheritDoc
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        $stateDef = $this->getState($model->{$this->getModelAccessKey()});
        return Html::el('span')->addAttributes(['class' => $stateDef['badge']])->addText(_($stateDef['label']));
    }

    /**
     * @param array[] $states
     */
    public function setStates(array $states) {
        $this->states = $states;
    }

    /**
     * @param string $state
     * @return array
     */
    public function getState(string $state): array {
        if (isset($this->states[$state])) {
            return $this->states[$state];
        }
        return ['badge' => '', 'label' => ''];
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        throw new NotImplementedException();
    }
}
