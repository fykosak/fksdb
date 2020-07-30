import { dispatchFetch } from '@fetchApi/netteFetch';
import {
    Action,
    Dispatch,
} from 'redux';
import { Team } from '../../helpers/interfaces';
import { ResponseData } from '../middleware/interfaces';

export const saveTeams = (accessKey: string, dispatch: Dispatch<Action>, teams: Team[]): Promise<any> => {
    const data = {act: 'routing-save', requestData: teams};
    return dispatchFetch<ResponseData>
    ('#', accessKey, dispatch, JSON.stringify(data), () => null, () => {
        setTimeout(() => {
            dispatch(removeUpdatesTeams());
        }, 5000);
    });
};

export const ACTION_REMOVE_UPDATED_TEAMS = 'ACTION_REMOVE_UPDATED_TEAMS';

const removeUpdatesTeams = (): Action => {
    return {
        type: ACTION_REMOVE_UPDATED_TEAMS,
    };
};
