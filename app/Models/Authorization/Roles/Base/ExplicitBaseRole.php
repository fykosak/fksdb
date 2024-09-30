<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Roles\Base;

use FKSDB\Models\Authorization\Roles\Role;
use Nette\InvalidStateException;
use Nette\Utils\Html;

final class ExplicitBaseRole implements Role
{
    public const Cartesian = 'base.cartesian';
    public const SchoolManager = 'base.schoolManager';

    /** @phpstan-var self::* $roleId */
    private string $roleId;

    /**
     * @phpstan-param self::* $roleId
     */
    public function __construct(string $roleId)
    {
        $this->roleId = $roleId;
    }

    public function getRoleId(): string
    {
        return $this->roleId;
    }

    public function badge(): Html
    {
        $className = 'badge bg-color-8';

        switch ($this->roleId) {
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
        switch ($this->roleId) {
            case self::SchoolManager:
                return 'správce dat škol';
            case self::Cartesian:
                return 'cokoli s čímkoli';
        }
        throw new InvalidStateException();//@phpstan-ignore-line
    }

    public function label(): string
    {
        switch ($this->roleId) {
            case self::SchoolManager:
                return 'School manager';
            case self::Cartesian:
                return 'Cartesian';
        }
        throw new InvalidStateException();//@phpstan-ignore-line
    }
}
