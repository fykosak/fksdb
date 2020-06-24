<?php

namespace FKSDB\Components\DatabaseReflection\ColumnFactories;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\Components\DatabaseReflection\OmittedControlException;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class StateRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class StateColumnFactory extends DefaultColumnFactory {
    /**
     * @var array[]
     */
    protected $states = [];

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        $state = $model->{$this->getModelAccessKey()};
        if (is_null($state)) {
            return NotSetBadge::getHtml();
        }
        $stateDef = $this->getState($state);
        return Html::el('span')->addAttributes(['class' => $stateDef['badge']])->addText(_($stateDef['label']));
    }

    /**
     * @param array $states
     * @return void
     */
    public function setStates(array $states) {
        $this->states = $states;
    }

    public function getState(string $state): array {
        if (isset($this->states[$state])) {
            return $this->states[$state];
        }
        return ['badge' => '', 'label' => ''];
    }

    public function createFormControl(...$args): BaseControl {
        throw new OmittedControlException();
    }
}
