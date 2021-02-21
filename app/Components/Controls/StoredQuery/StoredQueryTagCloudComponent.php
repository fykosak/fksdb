<?php

namespace FKSDB\Components\Controls\StoredQuery;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Models\ORM\Models\StoredQuery\ModelStoredQuery;
use FKSDB\Models\ORM\Models\StoredQuery\ModelStoredQueryTag;
use FKSDB\Models\ORM\Services\StoredQuery\ServiceStoredQueryTagType;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class StoredQueryTagCloudComponent extends BaseComponent {

    public const MODE_LIST = 'mode-list';
    public const MODE_DETAIL = 'mode-detail';
    private ServiceStoredQueryTagType $serviceStoredQueryTagType;
    /**
     * @persistent
     */
    public array $activeTagIds = [];

    final public function injectPrimary(ServiceStoredQueryTagType $serviceStoredQueryTagType): void {
        $this->serviceStoredQueryTagType = $serviceStoredQueryTagType;
    }

    public function handleOnClick(array $activeTagIds): void {
        $this->activeTagIds = $activeTagIds;
    }

    public function render(string $mode): void {
        $this->template->mode = $mode;
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.cloud.latte');
        $this->template->render();
    }

    public function renderList(): void {
        $this->template->tags = $this->serviceStoredQueryTagType->getTable();
        $this->template->activeTagIds = $this->activeTagIds;
        $this->template->nextActiveTagIds = $this->createNextActiveTagIds();
        $this->render(self::MODE_LIST);
    }

    public function renderDetail(ModelStoredQuery $query): void {
        $this->template->tags = $query->getStoredQueryTagTypes();
        $this->render(self::MODE_DETAIL);
    }

    private function createNextActiveTagIds(): array {
        $tags = $this->serviceStoredQueryTagType->getTable();
        $nextActiveTagIds = [];
        /** @var ModelStoredQueryTag $tag */
        foreach ($tags as $tag) {
            $activeTagIds = $this->activeTagIds;
            if (array_key_exists($tag->tag_type_id, $activeTagIds)) {
                unset($activeTagIds[$tag->tag_type_id]);
            } else {
                $activeTagIds[$tag->tag_type_id] = $tag->tag_type_id;
            }
            $nextActiveTagIds[$tag->tag_type_id] = $activeTagIds;
        }
        return $nextActiveTagIds;
    }
}
