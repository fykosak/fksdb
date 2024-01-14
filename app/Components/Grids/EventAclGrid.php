<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Grids\Components\BaseList;
use FKSDB\Components\Grids\Components\Renderer\RendererItem;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Services\LoginService;
use FKSDB\Models\UI\NotSetBadge;
use Fykosak\NetteORM\Selection\TypedSelection;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;
use Nette\Utils\Html;

/**
 * @phpstan-extends BaseList<LoginModel,array{role?:int|null}>
 */
final class EventAclGrid extends BaseList
{
    private EventModel $event;
    private LoginService $loginService;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container, 1024);
        $this->event = $event;
    }

    public function inject(LoginService $loginService): void
    {
        $this->loginService = $loginService;
    }

    protected function configure(): void
    {
        $this->paginate = false;
        $this->counter = false;
        $this->filtered = false;
        $this->mode = self::ModePanel;
        $this->setTitle(
            new RendererItem(
                $this->container,
                fn(LoginModel $login): string => $login->person ? $login->person->getFullName()
                    : ($login->login ?? ''),
                new Title(null, _('Person/Login'))
            )
        );
        $row1 = $this->createRow();
        $row1->addComponent(
            new RendererItem(
                $this->container,
                function (LoginModel $login) {
                    $container = Html::el('span');
                    foreach ($login->getExplicitEventRoles($this->event) as $grant) {
                        $container->addHtml($grant->badge());
                    }
                    return $container;
                },
                new Title(null, _('Role'))
            ),
            'role'
        );
        $row2 = $this->createRow();
        $row2->addComponent(
            new RendererItem(
                $this->container,
                function (LoginModel $login) {
                    $person = $login->person;
                    if (!$person) {
                        return NotSetBadge::getHtml();
                    }
                    $organizer = $person->getEventOrganizer($this->event);
                    if (!$organizer) {
                        return NotSetBadge::getHtml();
                    }
                    return $organizer->note ?? NotSetBadge::getHtml();
                },
                new Title(null, _('Role'))
            ),
            'organizer_role'
        );
    }

    /**
     * @phpstan-return TypedSelection<LoginModel>
     */
    protected function getModels(): TypedSelection
    {
        return $this->loginService->getTable()->where(':event_grant.event_id', $this->event->event_id);
    }
}
