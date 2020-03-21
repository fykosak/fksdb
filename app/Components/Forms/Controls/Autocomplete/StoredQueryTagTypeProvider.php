<?php

namespace FKSDB\Components\Forms\Controls\Autocomplete;

use FKSDB\ORM\Services\StoredQuery\ServiceStoredQueryTagType;
use Nette\Database\Table\Selection;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class StoredQueryTagTypeProvider implements IFilteredDataProvider {

    const DESCRIPTION = 'description';

    /**
     * @var \FKSDB\ORM\Services\StoredQuery\ServiceStoredQueryTagType
     */
    private $serviceStoredQueryTagType;

    /**
     * @var Selection
     */
    private $searchTable;

    /**
     * StoredQueryTagTypeProvider constructor.
     * @param \FKSDB\ORM\Services\StoredQuery\ServiceStoredQueryTagType $serviceStoredQueryTagType
     */
    function __construct(ServiceStoredQueryTagType $serviceStoredQueryTagType) {
        $this->serviceStoredQueryTagType = $serviceStoredQueryTagType;
        $this->searchTable = $this->serviceStoredQueryTagType->getTable();
    }

    /**
     * Prefix search.
     *
     * @param string $search
     * @return array
     */
    public function getFilteredItems($search) {
        $search = trim($search);
        $search = str_replace(' ', '', $search);
        $this->searchTable
                ->where('name LIKE concat(?, \'%\') OR description LIKE concat(?, \'%\')', $search, $search);
        return $this->getItems();
    }

    /**
     * @param mixed $id
     * @return bool|mixed|\Nette\Database\Table\ActiveRow|Selection|null
     */
    public function getItemLabel($id) {
        $tagType = $this->serviceStoredQueryTagType->findByPrimary($id);
        return $tagType->name;
    }

    /**
     * @return array
     */
    public function getItems() {
        $tagTypes = $this->searchTable
                ->order('name');

        $result = [];
        foreach ($tagTypes as $tagType) {
            $result[] = [
                self::LABEL => $tagType->name,
                self::VALUE => $tagType->tag_type_id,
                self::DESCRIPTION => $tagType->description,
            ];
        }
        return $result;
    }

    /**
     * @param $id
     */
    public function setDefaultValue($id) {
        /* intentionally blank */
    }

}
