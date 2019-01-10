import { Action } from 'redux';
import { IInputConnectorItems } from '../reducers';

export const ACTION_SET_INITIAL_DATA = '@@input-connector/ACTION_SET_INITIAL_DATA';

export interface IActionSetInitialData extends Action<string> {
    data: IInputConnectorItems;
}

export const setInitialData = (data: IInputConnectorItems): IActionSetInitialData => {
    return {
        data,
        type: ACTION_SET_INITIAL_DATA,
    };
};

export const ACTION_CHANGE_DATA = '@@input-connector/ACTION_CHANGE_DATA';

export interface IActionChangeData extends Action<string> {
    key: string;
    value: number;
}

export const changeData = (key: string, value: number): IActionChangeData => {
    return {
        key,
        type: ACTION_CHANGE_DATA,
        value,
    };
};
