import { dispatchFetch } from '@FKSDB/Model/FrontEnd/Fetch/netteFetch';
import { ModelFyziklaniTeam } from '@FKSDB/Model/ORM/Models/Fyziklani/modelFyziklaniTeam';
import {
    Action,
    Dispatch,
} from 'redux';
import { ResponseData } from '../middleware/interfaces';

export const saveTeams = (dispatch: Dispatch<Action>, teams: ModelFyziklaniTeam[]): Promise<any> => {
    const data = {act: 'routing-save', requestData: teams};
    return dispatchFetch<ResponseData>('#', dispatch, JSON.stringify(data));
};
