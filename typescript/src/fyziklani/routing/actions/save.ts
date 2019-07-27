import { dispatchNetteFetch } from '@fetchApi/middleware/fetch';
import { Response } from '@fetchApi/middleware/interfaces';
import {
    Action,
    Dispatch,
} from 'redux';
import { Team } from '../../helpers/interfaces';
import { ResponseData } from '../middleware/interfaces';
import { Store as RoutingStore } from '../reducers/';

export const saveTeams = (accessKey: string, dispatch: Dispatch<Action>, teams: Team[]): Promise<Response<ResponseData>> => {
    const data = {act: 'routing-save', requestData: teams};
    return dispatchNetteFetch<Team[], ResponseData, RoutingStore>
    (accessKey, dispatch, data, () => null, () => {
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
