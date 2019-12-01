import { Room, Submits, Task, Team } from '../../helpers/interfaces';

export interface ResponseData {
    basePath: string;
    availablePoints: number[];
    gameEnd: string;
    gameStart: string;
    isOrg: boolean;
    lastUpdated: string;
    refreshDelay: number;
    submits: Submits;
    times: {
        toStart: number;
        toEnd: number;
        visible: boolean;
    };
    teams: Team[];
    tasks: Task[];
    rooms: Room[];
    categories: string[];
}
