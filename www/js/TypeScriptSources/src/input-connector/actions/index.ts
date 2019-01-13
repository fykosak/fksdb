import { Action } from 'redux';
import { InputConnectorItems } from '../reducers';

export const ACTION_SET_INITIAL_DATA = '@@input-connector/ACTION_SET_INITIAL_DATA';

export interface ActionSetInitialData extends Action<string> {
    data: InputConnectorItems;
}

export const setInitialData = (data: InputConnectorItems): ActionSetInitialData => {
    return {
        data,
        type: ACTION_SET_INITIAL_DATA,
    };
};

export const ACTION_CHANGE_DATA = '@@input-connector/ACTION_CHANGE_DATA';

export interface ActionChangeData extends Action<string> {
    key: string;
    value: number;
}

export const changeData = (key: string, value: number): ActionChangeData => {
    return {
        key,
        type: ACTION_CHANGE_DATA,
        value,
    };
};
