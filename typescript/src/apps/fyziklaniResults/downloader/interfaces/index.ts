import { Room, Submits, Task, Team } from '../../../fyziklani/helpers/interfaces';

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
    teams?: Team[];
    tasks?: Task[];
    categories?: string[];
}
