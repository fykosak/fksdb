<?php

declare(strict_types=1);

namespace FKSDB\Components\Application\Team\Processing;

use FKSDB\Components\EntityForms\Processing\Postprocessing;
use FKSDB\Models\Email\Source\FOF\Info\InfoEmail;
use FKSDB\Models\Email\Source\FOF\OrganizerInfo\OrganizerInfoEmail;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\Transitions\Machine\TeamMachine;
use Fykosak\NetteORM\Model\Model;
use Nette\DI\Container;

/**
 * @phpstan-extends Postprocessing<TeamModel2>
 */
final class SendInfoEmail extends Postprocessing
{
    private TeamMachine $machine;

    public function __construct(Container $container, TeamMachine $machine)
    {
        parent::__construct($container);
        $this->machine = $machine;
    }

    /**
     * @throws BadTypeException
     */
    public function __invoke(Model $model): void
    {
        $holder = $this->machine->createHolder($model);
        (new InfoEmail($this->container))->createAndSend(['holder' => $holder]);
        (new OrganizerInfoEmail($this->container))->createAndSend(['holder' => $holder]);
    }
}
