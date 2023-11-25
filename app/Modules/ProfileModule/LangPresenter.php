<?php

declare(strict_types=1);

namespace FKSDB\Modules\ProfileModule;

use FKSDB\Components\Controls\PreferredLangFormComponent;
use Fykosak\Utils\UI\PageTitle;

final class LangPresenter extends BasePresenter
{
    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Preferred language'), 'fas fa-language');
    }

    public function authorizedDefault(): bool
    {
        return true;
    }

    protected function createComponentPreferredLangForm(): PreferredLangFormComponent
    {
        return new PreferredLangFormComponent($this->getContext(), $this->getLoggedPerson());
    }
}
