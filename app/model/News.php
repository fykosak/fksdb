<?php

use FKSDB\Config\GlobalParameters;
use FKSDB\ORM\Models\ModelContest;
use Nette\SmartObject;

/**
 * Class News
 */
class News {
    use SmartObject;

    private GlobalParameters $globalParameters;

    /**
     * News constructor.
     * @param GlobalParameters $globalParameters
     */
    public function __construct(GlobalParameters $globalParameters) {
        $this->globalParameters = $globalParameters;
    }

    public function getNews(ModelContest $contest,string $lang): array {
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
