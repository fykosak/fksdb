<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use FKSDB\Exceptions\NotImplementedException;
use Nette\Utils\Html;

/**
 * Class StateRow
 * *
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
     * @param array $args
     * @return BaseControl
     * @throws NotImplementedException
     */
    public function createField(...$args): BaseControl {
        throw new NotImplementedException();
    }
}
