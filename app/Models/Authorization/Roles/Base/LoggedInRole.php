<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Roles\Base;

use FKSDB\Models\Authorization\Roles\ImplicitRole;
use FKSDB\Models\Authorization\Roles\Role;
use FKSDB\Models\ORM\Models\LoginModel;
use Nette\Utils\Html;

final class LoggedInRole implements Role, ImplicitRole
{
    public const RoleId = 'loggedIn'; //@phpcs:ignore

    private LoginModel $login;

    public function __construct(LoginModel $login)
    {
        $this->login = $login;
    }

    public function getModel(): LoginModel
    {
        return $this->login;
    }

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'me-2 badge bg-primary '])
            ->addText(
                ($this->login->person ? $this->login->person->getFullName() : 'NA')
                . ' (' . $this->login->login_id . ')'
            );
    }

    public function getRoleId(): string
    {
        return self::RoleId;
    }
}
