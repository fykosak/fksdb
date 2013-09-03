<?php

namespace PublicModule;

use Nette\Application\BadRequestException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class SubmitPresenter extends BasePresenter {

    public function actionUpload() {
        if (!$this->contestAuthorizator->isAllowed('submit', 'upload', $this->getSelectedContest())) {
            throw new BadRequestException('Nedostatečné oprávnění.', 403);
        }
    }

}
