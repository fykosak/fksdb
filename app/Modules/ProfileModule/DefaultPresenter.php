<?php

declare(strict_types=1);

namespace FKSDB\Modules\ProfileModule;

use Fykosak\Utils\UI\PageTitle;

class DefaultPresenter extends BasePresenter
{
    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('My profile'), 'fa fa-cogs');
    }
}
