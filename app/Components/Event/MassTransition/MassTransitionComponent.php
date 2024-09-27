<?php

declare(strict_types=1);

namespace FKSDB\Components\Event\MassTransition;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Fykosak\NetteORM\Selection\TypedSelection;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\Database\Table\Selection;
use Nette\DI\Container;
use Tracy\Debugger;
use Tracy\ILogger;

/**
 * @phpstan-template TModel of Model
 * @phpstan-type TMachine = Machine<ModelHolder<TModel,(FakeStringEnum&EnumColumn)>>
 */
final class MassTransitionComponent extends BaseComponent
{
    /** @phpstan-var TMachine */
    private Machine $machine;
    private Selection $query;

    /**
     * @phpstan-param TMachine $machine
     * @phpstan-param TypedGroupedSelection<TModel>|TypedSelection<TModel> $query
     */
    public function __construct(Container $container, Machine $machine, Selection $query)
    {
        parent::__construct($container);
        $this->query = $query;
        $this->machine = $machine;
    }

    final public function render(): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte', [
            'transitions' => $this->machine->getTransitions()->toArray(),
        ]);
    }

    public function handleTransition(string $name): void
    {
        $total = 0;
        $errored = 0;
        $transition = $this->machine->getTransitions()->filterById($name)->select();
        /** @var EventParticipantModel|TeamModel2 $model */
        foreach ($this->query as $model) {
            $holder = $this->machine->createHolder($model);
            $total++;
            try {
                $transition->execute($holder);
            } catch (\Throwable $exception) {
                $errored++;
                Debugger::log($exception, ILogger::EXCEPTION);
            }
        }
        $this->getPresenter()->flashMessage(
            sprintf(
                _('Total %d applications, state changed %d, unavailable %d.'),
                $total,
                $total - $errored,
                $errored
            )
        );
        $this->getPresenter()->redirect('this');
    }
}
