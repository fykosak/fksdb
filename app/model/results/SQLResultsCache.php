<?php

/**
 * Fill caclulated points into database.
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class SQLResultsCache {

    /**
     * @var \Nette\Database\Connection
     */
    private $connection;

    function __construct(\Nette\Database\Connection $connection) {
        $this->connection = $connection;
    }

    /**
     * 
     * @param ModelContest $contest
     * @param int $year
     */
    public function invalidateCache(ModelContest $contest = null, $year = null) {
        $data = array(
            'calc_points' => null,
        );
        $conditions = array('1 = 1');
        $params = array();
        if ($contest !== null) {
            $conditions[] = 'contest = ?';
            $params[] = $contest->contest_id;
        }
        if ($year !== null) {
            $conditions[] = 'year = ?';
            $params[] = $year;
        }
        $this->connection->exec('update submit set ? where (' . implode(') and (', $conditions) . ')', $data, $params);
    }
    
    public function f() {
        /*
         * update submit s
left join task t on s.task_id = t.task_id
set calc_points = (
	select IF(ct.study_year IN (6,7,8,9,1,2), 2 * s.raw_points, s.raw_points)
	from contestant ct
	where ct.ct_id = s.ct_id
)
where t.year = 26;
         */
    }


}

?>
