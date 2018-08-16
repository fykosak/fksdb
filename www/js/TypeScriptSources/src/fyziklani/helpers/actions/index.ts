import {
    IRoom,
    ITask,
    ITeam,
} from '../../../shared/interfaces';
import { IParams } from '../../../results/components/app';

export const ACTION_SET_INITIAL_PARAMS = '@@fyziklani/ACTION_SET_INITIAL_PARAMS';

export const setInitialParameters = (rooms: IRoom[], tasks: ITask[], teams: ITeam[], params: IParams) => {
    return {
        rooms,
        tasks,
        teams,
        ...params,
        type: ACTION_SET_INITIAL_PARAMS,
    };
};
