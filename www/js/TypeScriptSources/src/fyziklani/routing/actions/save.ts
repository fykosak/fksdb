import { Dispatch } from 'react-redux';
import { Action } from 'redux';
import { dispatchNetteFetch } from '../../../fetch-api/middleware/fetch';
import { ITeam } from '../../helpers/interfaces';
import { IResponse } from '../middleware/interfaces';
import { IFyziklaniRoutingStore } from '../reducers/';

export const saveTeams = (accessKey: string, dispatch: Dispatch<IFyziklaniRoutingStore>, teams: ITeam[]): Promise<any> => {
    const data = {act: 'routing-save', data: JSON.stringify(teams)};
    return dispatchNetteFetch<string, IResponse, IFyziklaniRoutingStore>
    (accessKey, dispatch, data, () => null, (d) => {
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
