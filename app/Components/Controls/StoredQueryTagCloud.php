<?php

namespace FKSDB\Components\Controls;

use FKSDB\ORM\Models\StoredQuery\ModelStoredQuery;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQueryTag;
use Nette\Application\UI\Control;
use Nette\InvalidArgumentException;
use FKSDB\ORM\ServicesMulti\ServiceMStoredQueryTag;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class StoredQueryTagCloud extends Control {

    public const MODE_LIST = 'mode-list';
    public const MODE_DETAIL = 'mode-detail';

    private ServiceMStoredQueryTag $serviceMStoredQueryTag;

    /**
     * @var ModelStoredQuery
     */
    private $modelStoredQuery;

    /**
     * @var callable[]
     */
    private $onClick;
    /** @var string */
    private $mode;
    /** @var int[] */
    private $activeTagIds = [];

    /**
     * StoredQueryTagCloud constructor.
     * @param string $mode
     * @param ServiceMStoredQueryTag $serviceMStoredQueryTag
     */
    public function __construct(string $mode, ServiceMStoredQueryTag $serviceMStoredQueryTag) {
        parent::__construct();
        $this->serviceMStoredQueryTag = $serviceMStoredQueryTag;
        $this->mode = $mode;
    }

    /**
     * @param ModelStoredQuery $modelStoredQuery
     * @return void
     */
    public function setModelStoredQuery(ModelStoredQuery $modelStoredQuery) {
        $this->modelStoredQuery = $modelStoredQuery;
    }

    /**
     * @param callable $callback
     * @return static
     */
    public function registerOnClick(callable $callback) {
        $this->onClick[] = $callback;
        return $this;
    }

    /**
     * @param array $activeTagIds
     * @return void
     */
    public function handleOnClick(array $activeTagIds) {
        $this->activeTagIds = $activeTagIds;
        foreach ($this->onClick as $callback) {
            call_user_func($callback, $activeTagIds);
        }
    }

    public function render() {
        switch ($this->mode) {
            case self::MODE_LIST:
                $this->template->tags = $this->serviceMStoredQueryTag->getMainService();
                $this->template->activeTagIds = $this->activeTagIds;
                $this->template->nextActiveTagIds = $this->createNextActiveTagIds();
                break;
            case self::MODE_DETAIL:
                $this->template->tags = $this->modelStoredQuery->getMStoredQueryTags();
                break;
            default :
                throw new InvalidArgumentException();
        }

        $this->template->mode = $this->mode;
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'StoredQueryTagCloud.latte');
        $this->template->render();
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
