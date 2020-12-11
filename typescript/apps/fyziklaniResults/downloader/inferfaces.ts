import { Submits } from '../../fyziklani/helpers/interfaces';
import { ModelFyziklaniTeam } from '../../../../app/Model/ORM/Models/Fyziklani/ModelFyziklaniTeam';
import { ModelFyziklaniTask } from '../../../../app/Model/ORM/Models/Fyziklani/ModelFyziklaniTask';

export interface ResponseData {
    availablePoints: number[];
    basePath: string;
    gameStart: string;
    gameEnd: string;
    times: {
        toStart: number;
        toEnd: number;
        visible: boolean;
    };
    lastUpdated: string;
    isOrg: boolean;
    refreshDelay: number;
    tasksOnBoard: number;

    submits: Submits;
    teams?: ModelFyziklaniTeam[];
    tasks?: ModelFyziklaniTask[];
    categories?: string[];
}
