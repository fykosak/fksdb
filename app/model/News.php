<?php

use FKSDB\Config\GlobalParameters;
use FKSDB\ORM\ModelContest;
use Nette\Object;

class News extends Object {
    /**
     * @var ServiceContest
     */
    /**
     * @var GlobalParameters
     */
    private $globalParameters;


    function __construct(GlobalParameters $globalParameters) {
        $this->globalParameters = $globalParameters;
    }

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
