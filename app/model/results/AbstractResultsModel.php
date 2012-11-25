<?php

/**
 * General results sheet with contestants and their ranks.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class AbstractResultsModel implements IResultsModel {

    /**
     * @param ModelCategory $category
     * @return array of Nette\Database\Row
     */
    public function getData($category) {
        $sql = $this->composeQuery($category);
        echo $sql ."<br><br>";
        $stmt = $this->connection->query($sql);
        $result = $stmt->fetchAll();

        // reverse iteration to get ranking ranges
        $nextSum = false; //because last sum can be null
        for ($i = count($result) - 1; $i >= 0; --$i) {
            if ($result[$i][self::ALIAS_SUM] !== $nextSum) {
                $result[$i][self::DATA_RANK_TO] = $i + 1;
            } else {
                $result[$i][self::DATA_RANK_TO] = $result[$i + 1][self::DATA_RANK_TO];
            }
            $nextSum = $result[$i][self::ALIAS_SUM];
        }

        return $result;
    }

    /**
     * Unused?
     * @return array
     */
    public function getMetaColumns() {
        return array(
            self::DATA_NAME,
            self::DATA_SCHOOL,
            self::DATA_RANK_FROM,
            self::DATA_RANK_TO,
        );
    }

    abstract protected function composeQuery($category);

    /**
     * @note Work only with numeric types.
     * @param type $conditions
     * @return type
     */
    protected function conditionsToWhere($conditions) {
        $where = array();
        foreach ($conditions as $col => $value) {
            if (is_array($value)) {
                $set = array();
                foreach ($value as $subvalue) {
                    $set[] = $subvalue === null ? 'NULL' : $subvalue;
                }
                $where[] = "$col IN (" . implode(',', $set) . ")";
            } else {
                $where[] = "$col = $value";
            }
        }
        return "(" . implode(') and (', $where) . ")";
    }

}

?>
