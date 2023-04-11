<?php

declare(strict_types=1);

namespace FKSDB\Components\Charts\Event\Model;

use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Models\Transitions\Machine\Machine;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponent;
use Nette\DI\Container;

class GraphComponent extends FrontEndComponent implements Chart
{
    private Machine $machine;

    public function __construct(Container $container, Machine $machine)
    {
        parent::__construct($container, 'event.model.graph');
        $this->machine = $machine;
    }

    /**
     * @return array[]
     */
    final public function getData(): array
    {
        $edges = [];
        $nodes = [];
        foreach ($this->machine->getTransitions() as $transition) {
            if (!isset($nodes[$transition->source->value])) {
                $nodes[$transition->source->value] = [
                    'label' => $transition->source->label(),
                    'type' => $transition->source->value === Machine::STATE_INIT ? 'init' : 'default',
                ];
            }
            if (!isset($nodes[$transition->target->value])) {
                $nodes[$transition->target->value] = [
                    'label' => $transition->target->label(),
                    'type' => $transition->target->value === Machine::STATE_INIT ? 'init' : 'default',
                ];
            }
            $edges[] = [
                'from' => $transition->source->value,
                'to' => $transition->target->value,
                'label' => $transition->getLabel(),
            ];
        }
        return ['nodes' => $nodes, 'links' => $edges];
    }

    public function getTitle(): string
    {
        return _('Model of event');
    }

    public function getDescription(): ?string
    {
        return null;
    }
}
