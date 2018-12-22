import { Action } from 'redux';
import { IInputConnectorItems } from '../reducers';

export const ACTION_SET_INITIAL_DATA = '@@input-connector/ACTION_SET_INITIAL_DATA';

export interface IActionSetInitialData extends Action {
    data: IInputConnectorItems;
}

export const setInitialData = (data: IInputConnectorItems): IActionSetInitialData => {
    return {
        data,
        type: ACTION_SET_INITIAL_DATA,
    };
};

export const ACTION_CHANGE_DATA = '@@input-connector/ACTION_CHANGE_DATA';

export const changeData = (key: string, value: number): Action & any => {
    return {
        key,
        type: ACTION_CHANGE_DATA,
        value,
    };
};
