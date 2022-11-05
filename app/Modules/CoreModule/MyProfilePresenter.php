<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

use FKSDB\Components\Controls\Person\ChangeEmailComponent;
use FKSDB\Components\EntityForms\AddressFormComponent;
use FKSDB\Models\ORM\Models\PostContactType;
use Fykosak\Utils\UI\PageTitle;

class MyProfilePresenter extends BasePresenter
{
    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('My profile'), 'fa fa-cogs');
    }

    public function titleEdit(): PageTitle
    {
        return new PageTitle(null, _('Update my profile'), 'fa fa-cogs');
    }

    public function titleChangeEmail(): PageTitle
    {
        return new PageTitle(null, _('Change mail'), 'fa fa-cogs');
    }

    public function actionChangeEmail(): void
    {
        /** @var ChangeEmailComponent $component */
        $component = $this->getComponent('changeEmail');
        $component->changeEmail();
    }

    public function renderDefault(): void
    {
        $this->template->person = $this->getLoggedPerson();
    }

    protected function createComponentDeliveryPostContactForm(): AddressFormComponent
    {
        return $this->createComponentPostContactForm(PostContactType::tryFrom(PostContactType::DELIVERY));
    }

    protected function createComponentPermanentPostContactForm(): AddressFormComponent
    {
        return $this->createComponentPostContactForm(PostContactType::tryFrom(PostContactType::PERMANENT));
    }

    private function createComponentPostContactForm(PostContactType $type): AddressFormComponent
    {
        return new AddressFormComponent($this->getContext(), $type, $this->getLoggedPerson());
    }

    protected function createComponentChangeEmail(): ChangeEmailComponent
    {
        return new ChangeEmailComponent($this->getContext(), $this->getLoggedPerson(), $this->getLang());
    }
}
