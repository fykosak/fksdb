<?php

declare(strict_types=1);

namespace FKSDB\Modules\ProfileModule;

use FKSDB\Models\ORM\Services\PersonInfoService;
use Fykosak\Utils\UI\PageTitle;

class ThemePresenter extends BasePresenter
{
    private PersonInfoService $personInfoService;

    public function inject(PersonInfoService $personInfoService): void
    {
        $this->personInfoService = $personInfoService;
    }

    public function authorizedDefault(): bool
    {
        return true;
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Choose theme'), 'fas fa-circle-half-stroke');
    }

    public function handleChoose(string $theme)
    {
        $this->personInfoService->storeModel(['theme' => $theme], $this->getLoggedPerson()->getInfo());
        $this->flashMessage(_('Theme has been chosen!'));
    }
}
