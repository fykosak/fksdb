<?php

use FKSDB\Config\GlobalParameters;
use FKSDB\ORM\Models\ModelContest;
use Nette\SmartObject;

/**
 * Class News
 */
class News {
    use SmartObject;

    /**
     * @var GlobalParameters
     */
    private $globalParameters;

    /**
     * News constructor.
     * @param GlobalParameters $globalParameters
     */
    public function __construct(GlobalParameters $globalParameters) {
        $this->globalParameters = $globalParameters;
    }

    /**
     * @param ModelContest $contest
     * @param $lang
     * @return array
     */
    public function getNews(ModelContest $contest, $lang) {
        $contestName = $this->globalParameters['contestMapping'][$contest->contest_id];
        if (!isset($this->globalParameters[$contestName]['news'][$lang])) {
            return [];
        }
        $news = $this->globalParameters[$contestName]['news'][$lang];
        if ($news) {
            return $news;
        } else {
            return [];
        }
    }

}
