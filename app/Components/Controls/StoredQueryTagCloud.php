<?php

namespace FKSDB\Components\Controls;

use FKSDB\ORM\Models\StoredQuery\ModelStoredQuery;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQueryTag;
use Nette\Application\UI\Control;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
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

    /**
     * @var ModelStoredQuery
     */
    private $modelStoredQuery;
    /** @var string */
    private $mode;

    /** @persistent */
    public $activeTagIds = [];

    /**
     * StoredQueryTagCloud constructor.
     * @param string $mode
     * @param Container $container
     */
    public function __construct(string $mode, Container $container) {
        parent::__construct($container);
        $this->mode = $mode;
    }

    /**
     * @param ServiceMStoredQueryTag $serviceMStoredQueryTag
     * @return void
     */
    public function injectPrimary(ServiceMStoredQueryTag $serviceMStoredQueryTag) {
        $this->serviceMStoredQueryTag = $serviceMStoredQueryTag;
    }

    /**
     * @param ModelStoredQuery $modelStoredQuery
     * @return void
     */
    public function setModelStoredQuery(ModelStoredQuery $modelStoredQuery) {
        $this->modelStoredQuery = $modelStoredQuery;
    }

    /**
     * @param array $activeTagIds
     * @return void
     */
    public function handleOnClick(array $activeTagIds) {
        $this->activeTagIds = $activeTagIds;
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
