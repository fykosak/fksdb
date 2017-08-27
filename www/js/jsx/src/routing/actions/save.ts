import { Dispatch } from 'react-redux';
import { ITeam } from '../interfaces';
import { IStore } from '../reducers/index';

export const ACTION_SAVE_ROUTING_START = 'ACTION_SAVE_ROUTING_START';

export const ACTION_SAVE_ROUTING_SUCCESS = 'ACTION_SAVE_ROUTING_SUCCESS';
export const ACTION_SAVE_ROUTING_FAIL = 'ACTION_SAVE_ROUTING_FAIL';

export const saveTeams = (dispatch: Dispatch<IStore>, teams: ITeam[]) => {
    const netteJQuery: any = $;
    netteJQuery.nette.ajax({
        data: { data: JSON.stringify(teams) },
        error: (e) => {
            dispatch(saveFail(e));
            throw e;
        },
        success: () => {
            dispatch(saveSuccess());
        },
        type: 'POST',
    });
    return {
        type: ACTION_SAVE_ROUTING_START,
    };
};

const saveSuccess = () => {
    return {
        type: ACTION_SAVE_ROUTING_SUCCESS,
    };
};

const saveFail = (e) => {
    return {
        error: e,
        type: ACTION_SAVE_ROUTING_FAIL,
    };
};
