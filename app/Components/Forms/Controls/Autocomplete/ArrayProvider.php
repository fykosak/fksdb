<?php

namespace FKSDB\Components\Forms\Controls\Autocomplete;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ArrayProvider implements IFilteredDataProvider {

    private $data;
    private $labelById;

    function __construct(array $data) {
        $this->data = array();
        $this->labelById = $data;
        foreach ($data as $id => $label) {
            $this->data[] = array(
                self::VALUE => $id,
                self::LABEL => $label,
            );
        }
    }

    /**
     * Prefix search.
     *
     * @param string $search
     * @return array
     */
    public function getFilteredItems($search) {
        $result = array();
        foreach ($this->data as $item) {
            $label = $item[self::LABEL];
            if (mb_substr($label, 0, mb_strlen($search)) == $search) {
                $result[] = $item;
            }
        }
        return $result;
    }

    public function getItemLabel($id) {
        return $this->labelById[$id];
    }

    public function getItems() {
        return $this->data;
    }

    public function setDefaultValue($id) {
        /* intentionally blank */
    }


//put your code here
}
