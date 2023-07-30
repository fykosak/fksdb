<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\StoredQuery;

use FKSDB\Models\ORM\Services\StoredQuery\TagTypeService;
use Fykosak\Utils\BaseComponent\BaseComponent;

class StoredQueryTagCloudComponent extends BaseComponent
{
    private TagTypeService $storedQueryTagTypeService;
    /**
     * @persistent
     * @internal
     * @phpstan-var array<int,bool>
     */
    public array $activeTagIds = [];

    final public function injectPrimary(TagTypeService $storedQueryTagTypeService): void
    {
        $this->storedQueryTagTypeService = $storedQueryTagTypeService;
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
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.cloud.list.latte', [
            'tags' => $this->storedQueryTagTypeService->getTable(),
            'activeTagIds' => $this->activeTagIds,
        ]);
    }

    /**
     * @return int[]
     */
    public function getActiveTagIds(): array
    {
        return array_keys($this->activeTagIds);
    }
}
