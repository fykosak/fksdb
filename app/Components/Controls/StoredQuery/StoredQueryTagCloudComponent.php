<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\StoredQuery;

use Fykosak\Utils\BaseComponent\BaseComponent;
use FKSDB\Models\ORM\Models\StoredQuery\ModelStoredQuery;
use FKSDB\Models\ORM\Services\StoredQuery\ServiceStoredQueryTagType;

class StoredQueryTagCloudComponent extends BaseComponent
{
    private ServiceStoredQueryTagType $serviceStoredQueryTagType;
    /**
     * @persistent
     * @internal
     */
    public array $activeTagIds = [];

    final public function injectPrimary(ServiceStoredQueryTagType $serviceStoredQueryTagType): void
    {
        $this->serviceStoredQueryTagType = $serviceStoredQueryTagType;
    }

    public function handleOnClick(int $activeTagId): void
    {
        if (isset($this->activeTagIds[$activeTagId])) {
            unset($this->activeTagIds[$activeTagId]);
        } else {
            $this->activeTagIds[$activeTagId] = true;
        }
    }

    final public function renderList(): void
    {
        $this->template->tags = $this->serviceStoredQueryTagType->getTable();
        $this->template->activeTagIds = $this->activeTagIds;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.cloud.list.latte');
    }

    final public function renderDetail(ModelStoredQuery $query): void
    {
        $this->template->tags = $query->getStoredQueryTagTypes();
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.cloud.detail.latte');
    }

    public function getActiveTagIds(): array
    {
        return array_keys($this->activeTagIds);
    }
}
