<?php

declare(strict_types=1);

namespace FKSDB\Components\Charts\Event\Model;

use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Models\Transitions\Machine\Machine;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponent;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;
use Nette\Utils\Html;

/**
 * @phpstan-template TMachine of Machine
 */
class GraphComponent extends FrontEndComponent implements Chart
{
    /** @phpstan-var TMachine */
    private Machine $machine;

    /**
     * @phpstan-param TMachine $machine
     */
    public function __construct(Container $container, Machine $machine)
    {
        parent::__construct($container, 'event.model.graph');
        $this->machine = $machine;
    }

    /**
     * @phpstan-return array{nodes:array<string,array{label:string,type:string}>,links:array<int,array{from:string,to:string,label:string|Html}>}
     */
    final public function getData(): array
    {
        $edges = [];
        $nodes = [];
        foreach ($this->machine->getTransitions()->toArray() as $transition) {
            if (!isset($nodes[$transition->source->value])) {
                $nodes[$transition->source->value] = [
                    'label' => $transition->source->label(),
                    'type' => 'default',
                ];
            }
            if (!isset($nodes[$transition->target->value])) {
                $nodes[$transition->target->value] = [
                    'label' => $transition->target->label(),
                    'type' => 'default',
                ];
            }
            $edges[] = [
                'from' => $transition->source->value,
                'to' => $transition->target->value,
                'label' => $transition->label->toHtml(),
                'behaviorType' => $transition->behaviorType->value,
            ];
        }
        return ['nodes' => $nodes, 'links' => $edges];
    }

    public function getTitle(): Title
    {
        return new Title(null, _('Model of event'), 'fas fa-diagram-project');
    }

    public function getDescription(): ?string
    {
        return null;
    }
}
