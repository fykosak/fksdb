<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use FKSDB\Exceptions\NotImplementedException;
use Nette\Utils\Html;

/**
 * Class StateRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class StateRow extends DefaultRow {
    /**
     * @var array[]
     */
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

    /**
     * @param array $args
     * @return BaseControl
     * @throws NotImplementedException
     */
    public function createFormControl(...$args): BaseControl {
        throw new NotImplementedException();
    }
}
