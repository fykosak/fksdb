import { dispatchFetch } from '@fetchApi/netteFetch';
import {
    Action,
    Dispatch,
} from 'redux';
import { ResponseData } from '../middleware/interfaces';
import { ModelFyziklaniTeam } from '../../../../../app/Model/ORM/Models/Fyziklani/ModelFyziklaniTeam';

export const saveTeams = (dispatch: Dispatch<Action>, teams: ModelFyziklaniTeam[]): Promise<any> => {
    const data = {act: 'routing-save', requestData: teams};
    return dispatchFetch<ResponseData>('#', dispatch, JSON.stringify(data));
};
