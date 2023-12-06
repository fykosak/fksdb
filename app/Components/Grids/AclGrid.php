<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Grids\Components\BaseList;
use FKSDB\Components\Grids\Components\Renderer\RendererItem;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\GrantModel;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\OrganizerModel;
use FKSDB\Models\ORM\Models\RoleModel;
use FKSDB\Models\ORM\Services\LoginService;
use FKSDB\Models\ORM\Services\RoleService;
use FKSDB\Models\UI\NotSetBadge;
use Fykosak\NetteORM\Selection\TypedSelection;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * @phpstan-extends BaseList<LoginModel,array{role?:int|null}>
 */
final class AclGrid extends BaseList
{
    private ContestModel $contest;
    private LoginService $loginService;
    private RoleService $roleService;

    public function __construct(Container $container, ContestModel $contest)
    {
        parent::__construct($container, 1024);
        $this->contest = $contest;
    }

    public function inject(LoginService $loginService, RoleService $roleService): void
    {
        $this->loginService = $loginService;
        $this->roleService = $roleService;
    }

    protected function configure(): void
    {
        $this->paginate = false;
        $this->counter = false;
        $this->filtered = true;
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
                    /** @var GrantModel $grant */
                    foreach ($login->getGrants()->where('contest_id', $this->contest->contest_id) as $grant) {
                        $container->addHtml($grant->role->badge());
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
                    /** @var OrganizerModel|null $organizer */
                    $organizer = $person->getOrganizers($this->contest)->fetch();
                    if (!$organizer) {
                        return NotSetBadge::getHtml();
                    }
                    return $organizer->role ?? NotSetBadge::getHtml();
                },
                new Title(null, _('Role'))
            ),
            'organizer_role'
        );
    }

    protected function configureForm(Form $form): void
    {
        $items = [];
        /** @var RoleModel $role */
        foreach ($this->roleService->getTable() as $role) {
            $items[$role->role_id] = $role->name;
        }
        $form->addSelect('role', _('Role'), $items)->setPrompt(_('--select role--'));
    }

    /**
     * @phpstan-return TypedSelection<LoginModel>
     */
    protected function getModels(): TypedSelection
    {
        $query = $this->loginService->getTable()->where(':grant.contest_id', $this->contest->contest_id);
        if (isset($this->filterParams['role'])) {
            $query->where(':grant.role_id', $this->filterParams['role']);
        }
        return $query;
    }
}
