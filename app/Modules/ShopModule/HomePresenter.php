<?php

declare(strict_types=1);

namespace FKSDB\Modules\ShopModule;

use Fykosak\Utils\UI\PageTitle;

final class HomePresenter extends BasePresenter
{
    public function authorizedDefault(): bool
    {
        return true;
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Shop & payments'));
    }
}
