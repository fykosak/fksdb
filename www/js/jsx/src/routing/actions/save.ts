import { Dispatch } from 'react-redux';
import { ITeam } from '../interfaces';
import { IStore } from '../reducers/index';

export const ACTION_SAVE_ROUTING_START = 'ACTION_SAVE_ROUTING_START';

export const ACTION_SAVE_ROUTING_SUCCESS = 'ACTION_SAVE_ROUTING_SUCCESS';
export const ACTION_SAVE_ROUTING_FAIL = 'ACTION_SAVE_ROUTING_FAIL';

export const ACTION_REMOVE_UPDATED_TEAMS = 'ACTION_REMOVE_UPDATED_TEAMS';

export const saveTeams = (dispatch: Dispatch<IStore>, teams: ITeam[]) => {
    const netteJQuery: any = $;
    netteJQuery.nette.ajax({
        data: { data: JSON.stringify(teams) },
        error: (e) => {
            dispatch(saveFail(e));
            throw e;
        },
        success: (d) => {
            dispatch(saveSuccess(d));
            setTimeout(() => {
                dispatch(removeUpdatesTeams());
            }, 5000);
        },
        type: 'POST',
    });
    return {
        type: ACTION_SAVE_ROUTING_START,
    };
};

const saveSuccess = (data) => {
    return {
        data,
        type: ACTION_SAVE_ROUTING_SUCCESS,
    };
};

const removeUpdatesTeams = () => {
    return {
        type: ACTION_REMOVE_UPDATED_TEAMS,
    };
};

const saveFail = (e) => {
    return {
        error: e,
        type: ACTION_SAVE_ROUTING_FAIL,
    };
};
