<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

use FKSDB\Components\EntityForms\PublicSchoolForm;
use Fykosak\Utils\UI\PageTitle;

class SchoolPresenter extends BasePresenter
{
    public function requiresLogin(): bool
    {
        return false;
    }

    public function authorizedCreate(): bool
    {
        return true;
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create school'));
    }

    public function authorizedSuccess(): bool
    {
        return true;
    }

    public function titleSuccess(): PageTitle
    {
        return new PageTitle(null, _('Your school has been successfully added!'));
    }

    protected function createComponentForm(): PublicSchoolForm
    {
        return new PublicSchoolForm($this->getContext());
    }
}
