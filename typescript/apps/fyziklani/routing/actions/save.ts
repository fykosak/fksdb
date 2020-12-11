import { dispatchFetch } from '@fetchApi/netteFetch';
import {
    Action,
    Dispatch,
} from 'redux';
import { Team } from '../../helpers/interfaces';
import { ResponseData } from '../middleware/interfaces';

export const saveTeams = (dispatch: Dispatch<Action>, teams: Team[]): Promise<any> => {
    const data = {act: 'routing-save', requestData: teams};
    return dispatchFetch<ResponseData>('#', dispatch, JSON.stringify(data));
};
