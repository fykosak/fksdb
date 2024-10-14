<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Grant;

use FKSDB\Models\Authorization\Roles\Role;
use FKSDB\Models\ORM\Models\LoginModel;
use Fykosak\NetteORM\Model\Model;
use Nette\InvalidStateException;
use Nette\Utils\Html;

/**
 * @property-read int $grant_id
 * @property-read int $login_id
 * @property-read LoginModel $login
 * @property-read string|self::* $role
 */
final class BaseGrantModel extends Model implements Role
{
    public const Cartesian = 'base.cartesian';//phpcs:ignore
    public const SchoolManager = 'base.schoolManager';//phpcs:ignore

    public function badge(): Html
    {
        $className = 'badge bg-color-8';

        switch ($this->role) {
            case self::SchoolManager:
                $className = 'bg-color-5';
                break;
            case self::Cartesian:
                $className = 'bg-color-11';
                break;
        }
        return Html::el('span')
            ->addAttributes(['class' => 'me-2 badge ' . $className])
            ->addText($this->label() . ' (' . $this->description() . ')');
    }

    public function description(): string
    {
        switch ($this->role) {
            case self::SchoolManager:
                return 'správce dat škol';
            case self::Cartesian:
                return 'cokoli s čímkoli';
        }
        throw new InvalidStateException();
    }

    public function getRoleId(): string
    {
        return $this->role;
    }

    public function label(): string
    {
        switch ($this->role) {
            case self::SchoolManager:
                return 'School manager';
            case self::Cartesian:
                return 'Cartesian';
        }
        throw new InvalidStateException();
    }
}
