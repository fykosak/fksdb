import { Dispatch } from 'react-redux';
import { Action } from 'redux';
import { dispatchNetteFetch } from '../../../fetch-api/middleware/fetch';
import { IResponse } from '../../../fetch-api/middleware/interfaces';
import { ITeam } from '../../helpers/interfaces';
import { IResponseData } from '../middleware/interfaces';
import { IFyziklaniRoutingStore } from '../reducers/';

export const saveTeams = (accessKey: string, dispatch: Dispatch<IFyziklaniRoutingStore>, teams: ITeam[]): Promise<IResponse<IResponseData>> => {
    const data = {act: 'routing-save', requestData: teams};
    return dispatchNetteFetch<ITeam[], IResponseData, IFyziklaniRoutingStore>
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
