<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\StoredQuery;

use FKSDB\Models\ORM\Models\StoredQuery\QueryModel;
use FKSDB\Models\ORM\Services\StoredQuery\TagTypeService;
use Fykosak\Utils\BaseComponent\BaseComponent;

class StoredQueryTagCloudComponent extends BaseComponent
{
    private TagTypeService $storedQueryTagTypeService;
    /**
     * @persistent
     * @internal
     */
    public array $activeTagIds = [];

    final public function injectPrimary(TagTypeService $storedQueryTagTypeService): void
    {
        $this->storedQueryTagTypeService = $storedQueryTagTypeService;
    }

    public function handleOnClick(int $activeTagId, bool $active): void
    {
        if ($active) {
            $this->activeTagIds[$activeTagId] = true;
        } else {
            unset($this->activeTagIds[$activeTagId]);
        }
    }

    final public function renderList(): void
    {
        $this->template->tags = $this->storedQueryTagTypeService->getTable();
        $this->template->activeTagIds = $this->activeTagIds;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.cloud.list.latte');
    }

    final public function renderDetail(QueryModel $query): void
    {
        $this->template->tags = $query->getStoredQueryTagTypes();
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.cloud.detail.latte');
    }

    public function getActiveTagIds(): array
    {
        return array_keys($this->activeTagIds);
    }
}
