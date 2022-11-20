<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Fyziklani\Submit;

use FKSDB\Models\Fyziklani\Submit\ClosedSubmittingException;
use FKSDB\Models\ORM\Models\EventModel;
use Nette\DI\Container;

class CtyrbojPointsEntryComponent extends PointsEntryComponent
{

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container, $event, 'ctyrboj.submit-form');
    }

    /**
     * @throws ClosedSubmittingException
     */
    protected function innerHandleSave(array $data): void
    {
        $handler = $this->event->createGameHandler($this->getContext());
        $handler->preProcess($this->getLogger(), $data['code'], null);
    }
}
