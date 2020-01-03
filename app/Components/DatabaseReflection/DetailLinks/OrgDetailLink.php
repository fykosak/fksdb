<?php

namespace FKSDB\Components\DatabaseReflection\Links;

use FKSDB\ORM\Models\ModelOrg;
use Nette\Application\UI\InvalidLinkException;

/**
 * Class OrgLink
 * @package FKSDB\Components\DatabaseReflection\Links
 */
class OrgDetailLink extends AbstractLink {
    /**
     * @param ModelOrg $model
     * @return string|void
     * @throws InvalidLinkException
     */
    protected function createLink($model): string {
        return $this->presenterComponent->getPresenter()->link(':Org:org:detail', [
            'contestId' => $model->contest_id,
            'id' => $model->org_id,
        ]);
    }

    /**
     * @return string
     */
    protected function getText(): string {
        return _('Detail');
    }
}
