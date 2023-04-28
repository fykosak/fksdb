<?php

declare(strict_types=1);

namespace FKSDB\Modules\ProfileModule;

use FKSDB\Components\Controls\PreferredLangFormComponent;
use Fykosak\Utils\UI\PageTitle;

class LangPresenter extends BasePresenter
{
    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Preferred language'), 'fas fa-language');
    }

    protected function createComponentPreferredLangForm(): PreferredLangFormComponent
    {
        return new PreferredLangFormComponent($this->getContext(), $this->getLoggedPerson());
    }
}
