import { dispatchNetteFetch } from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/netteFetch';
import { ModelFyziklaniTeam } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniTeam';
import {
    Action,
    Dispatch,
} from 'redux';
import { ResponseData } from '../middleware/interfaces';

export const saveTeams = (dispatch: Dispatch<Action>, teams: ModelFyziklaniTeam[]): Promise<any> => {
    const data = {act: 'routing-save', requestData: teams};
    return dispatchNetteFetch<ResponseData>('#', dispatch, JSON.stringify(data));
};
