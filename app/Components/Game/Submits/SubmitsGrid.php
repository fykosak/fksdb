<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Submits;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\Fyziklani\SubmitService;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;

abstract class SubmitsGrid extends BaseGrid
{
    protected SubmitService $submitService;
    protected EventModel $event;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    final public function injectServiceFyziklaniSubmit(SubmitService $submitService): void
    {
        $this->submitService = $submitService;
    }

    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);
        if ($this->event->event_type_id === 1) {
            $this->addLinkButton(':Game:Submit:edit', 'edit', _('Edit'), false, ['id' => 'fyziklani_submit_id']);
            $this->addLinkButton(':Game:Submit:detail', 'detail', _('Detail'), false, ['id' => 'fyziklani_submit_id']);
        }
    }
}
