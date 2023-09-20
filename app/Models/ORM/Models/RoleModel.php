<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model;
use Nette\Utils\Html;

/**
 * @property-read int $role_id
 * @property-read string $name
 * @property-read string|null $description
 */
final class RoleModel extends Model
{
    public const CONTESTANT = 'contestant';
    public const ORGANIZER = 'organizer';
    public const REGISTERED = 'registered';
    public const GUEST = 'guest';

    public function badge(): Html
    {
        $className = 'badge bg-color-8';

        switch ($this->role_id) {
            case 2:
                $className = 'badge bg-color-1';
                break;
            case 4:
                $className = 'badge bg-color-6';
                break;
            case 5:
                $className = 'badge bg-color-7';
                break;
            case 6:
                $className = 'badge bg-color-9';
                break;
            case 7:
                $className = 'badge bg-color-4';
                break;
            case 8:
            case 9:
                $className = 'badge bg-color-2';
                break;
            case 12:
                $className = 'badge bg-color-5';
                break;
            case 11:
            case 13:
            case 14:
                $className = 'badge bg-color-10';
                break;
            case 100:
                $className = 'badge bg-color-3';
                break;
            case 1000:
                $className = 'badge bg-color-11';
                break;
        }
        return Html::el('span')
            ->addAttributes(['class' => $className])
            ->addText($this->name . ' (' . $this->description . ')');
    }
}
