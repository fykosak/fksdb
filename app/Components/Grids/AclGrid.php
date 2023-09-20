<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Components\Grids\Components\Renderer\RendererItem;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\GrantModel;
use Fykosak\Utils\UI\Title;
use Nette\Database\Table\Selection;
use Nette\DI\Container as DIContainer;

class AclGrid extends BaseGrid
{

    private ContestModel $contest;

    public function __construct(DIContainer $container, ContestModel $contest)
    {
        parent::__construct($container, 1024);
        $this->contest = $contest;
    }

    protected function configure(): void
    {
        $this->paginate = false;
        $this->addColumn(
            new RendererItem(
                $this->container,
                fn(GrantModel $grant) => $grant->login->person ? $grant->login->person->getFullName()
                    : $grant->login->login,
                new Title(null, _('Person/login'))
            ),
            'name'
        );
        $this->addColumn(
            new RendererItem(
                $this->container,
                fn(GrantModel $grant) => $grant->role->badge(),
                new Title(null, _('Role'))
            ),
            'role'
        );
    }

    protected function getModels(): Selection
    {
        return $this->contest->related(DbNames::TAB_GRANT)->order('login_id');
    }
}
