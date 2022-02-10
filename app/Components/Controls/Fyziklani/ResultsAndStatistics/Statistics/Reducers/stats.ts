import { ACTION_SET_NEW_STATE, ActionSetNewState } from '../actions';

export interface State {
    activePoints?: number;
    taskId?: number;
    firstTeamId?: number;
    secondTeamId?: number;
    aggregationTime?: number;
    fromDate?: Date;
    toDate?: Date;
}

export const stats = (state: State = {aggregationTime: 5 * 60 * 1000}, action: ActionSetNewState): State => {
    switch (action.type) {
        case ACTION_SET_NEW_STATE:
            return {...state, ...action.data}
        default:
            return state;
    }
};
