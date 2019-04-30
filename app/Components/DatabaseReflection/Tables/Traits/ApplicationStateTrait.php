<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\Controls\Helpers\Badges\NotSetBadge;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Utils\Html;

/**
 * Trait ApplicationStateTrait
 * @package FKSDB\Components\DatabaseReflection
 */
trait ApplicationStateTrait {
    /**
     * @var array
     */
    private $classNameMapping = [
        'badge badge-1' => [
            'applied',
            'applied.nodsef',
            'applied.notsaf',
            'applied.tsaf',
            'approved',
        ],
        'badge badge-2' => [
            'interested',
            'pending',
        ],
        'badge badge-3' => ['participated'],
        'badge badge-4' => ['missed'],
        'badge badge-5' => ['disqualified'],
        'badge badge-6' => ['rejected', 'cancelled'],
        'badge badge-7' => ['paid'],
        'badge badge-8' => ['out_of_db'],
        'badge badge-9' => [
            'spare',
            'spare1',
            'spare2',
            'spare3',
            'spare.tsaf',
            'auto.spare',
        ],
        'badge badge-10' => [
            'invited',
            'invited1',
            'invited2',
            'invited3',
            'auto.invited',
        ],
    ];

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Status');
    }

    /**
     * @param AbstractModelSingle $model
     * @param string $fieldName
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model, string $fieldName): Html {
        $state = $model->{$this->getModelAccessKey()};
        if (\is_null($state)) {
            return NotSetBadge::getHtml();
        }
        $elementClassName = '';
        foreach ($this->classNameMapping as $className => $states) {
            if (\in_array($state, $states)) {
                $elementClassName = $className;
            }
        }
        return Html::el('span')->addAttributes(['class' => $elementClassName])->addText(_($state));
    }

    /**
     * @return string
     */
    abstract public function getModelAccessKey(): string;
}
