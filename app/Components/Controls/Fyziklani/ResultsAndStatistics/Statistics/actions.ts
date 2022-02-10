import { Action } from 'redux';
import { State } from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Statistics/Reducers/stats';

export const ACTION_SET_NEW_STATE = '@@fyziklani/ACTION_SET_NEW_STATE'

export interface ActionSetNewState extends Action<string> {
    data: State;
}

export const setNewState = (data: State): ActionSetNewState => {
    return {
        data,
        type: ACTION_SET_NEW_STATE,
    };
};
