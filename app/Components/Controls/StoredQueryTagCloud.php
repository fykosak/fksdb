<?php

namespace FKSDB\Components\Controls;

use FKSDB\ORM\Models\StoredQuery\ModelStoredQuery;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQueryTag;
use Nette\DI\Container;
use FKSDB\ORM\ServicesMulti\ServiceMStoredQueryTag;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class StoredQueryTagCloud extends BaseComponent {

    const MODE_LIST = 'mode-list';
    const MODE_DETAIL = 'mode-detail';

    /**
     * @var ServiceMStoredQueryTag
     */
    private $serviceMStoredQueryTag;

    /** @var string */
    private $mode;

    /** @persistent */
    public $activeTagIds = [];

    /**
     * StoredQueryTagCloud constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        parent::__construct($container);
    }

    /**
     * @param ServiceMStoredQueryTag $serviceMStoredQueryTag
     * @return void
     */
    public function injectPrimary(ServiceMStoredQueryTag $serviceMStoredQueryTag) {
        $this->serviceMStoredQueryTag = $serviceMStoredQueryTag;
    }

    /**
     * @param array $activeTagIds
     * @return void
     */
    public function handleOnClick(array $activeTagIds) {
        $this->activeTagIds = $activeTagIds;
    }

    /**
     * @return void
     */
    public function render(string $mode) {
        $this->template->mode = $mode;
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'StoredQueryTagCloud.latte');
        $this->template->render();
    }

    public function renderList() {
        $this->template->tags = $this->serviceMStoredQueryTag->getMainService();
        $this->template->activeTagIds = $this->activeTagIds;
        $this->template->nextActiveTagIds = $this->createNextActiveTagIds();
        $this->render(self::MODE_LIST);
    }

    /**
     * @param ModelStoredQuery $query
     * @return void
     */
    public function renderDetail(ModelStoredQuery $query) {
        $this->template->tags = $query->getMStoredQueryTags();
        $this->render(self::MODE_DETAIL);
    }

    /**
     * @return array
     */
    private function createNextActiveTagIds() {
        $tags = $this->serviceMStoredQueryTag->getMainService();
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
