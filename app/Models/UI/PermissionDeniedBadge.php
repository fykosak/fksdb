<?php

declare(strict_types=1);

namespace FKSDB\Models\UI;

use Nette\Utils\Html;

class PermissionDeniedBadge
{
    public static function getHtml(): Html
    {
        return Html::el('span')->addAttributes(['class' => 'me-1 badge bg-danger'])->addText(_('Permissions denied'));
    }
}
