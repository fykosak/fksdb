import { Dispatch } from 'react-redux';
import { IStore } from '../reducers/';
import { ITeam } from '../../helpers/interfaces';
import { dispatchNetteFetch } from '../../../fetch-api/middleware/fetch';
import { Action } from 'redux';

export const ACTION_SAVE_ROUTING_START = 'ACTION_SAVE_ROUTING_START';

export const ACTION_SAVE_ROUTING_SUCCESS = 'ACTION_SAVE_ROUTING_SUCCESS';
export const ACTION_SAVE_ROUTING_FAIL = 'ACTION_SAVE_ROUTING_FAIL';

export const ACTION_REMOVE_UPDATED_TEAMS = 'ACTION_REMOVE_UPDATED_TEAMS';

export const saveTeams = (dispatch: Dispatch<IStore>, teams: ITeam[]): Promise<any> => {
    const data = {act: 'routing-save', data: JSON.stringify(teams)};
    return dispatchNetteFetch<string, any, IStore>
    ('@@fyziklani/routing', dispatch, data, () => null, (d) => {

        setTimeout(() => {
            dispatch(removeUpdatesTeams());
        }, 5000);
    });

};

const removeUpdatesTeams = (): Action => {
    return {
        type: ACTION_REMOVE_UPDATED_TEAMS,
    };
};
