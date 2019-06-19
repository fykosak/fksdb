<?php

namespace FKSDB\Components\Controls\Stalking;

/**
 * Class BaseInfo
 * @package FKSDB\Components\Controls\Stalking
 */
class BaseInfo extends AbstractStalkingComponent {

    public function render() {
        $this->beforeRender();
        $this->template->fields = [
            'born', 'born_id', 'birthplace',
            'id_number', 'employer', 'uk_login',
            'health_insurance', 'citizenship', 'account'
            , 'career', 'homepage', 'im',
            'linkedin_id', 'note','origin',
        ];
        $this->template->info = $this->modelPerson->getInfo();
        $this->template->person = $this->modelPerson;
        $this->template->setFile(__DIR__ . '/BaseInfo.latte');
        $this->template->render();
    }

    /**
     * @return string
     */
    protected function getHeadline(): string {
        return _('Base info');
    }

    /**
     * @return string[]
     */
    protected function getAllowedPermissions(): array {
        return [AbstractStalkingComponent::PERMISSION_FULL, AbstractStalkingComponent::PERMISSION_RESTRICT, self::PERMISSION_BASIC];
    }
}
