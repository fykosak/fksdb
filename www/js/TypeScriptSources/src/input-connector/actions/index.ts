import { IInputConnectorItems } from '../reducers';

export const ACTION_SET_INITIAL_DATA = '@@input-connector/ACTION_SET_INITIAL_DATA';

export function setInitialData(data: IInputConnectorItems) {
    return {
        data,
        type: ACTION_SET_INITIAL_DATA,
    };
}

export const ACTION_CHANGE_DATA = '@@input-connector/ACTION_CHANGE_DATA';

export const changeData = (key: string, value: number) => {
    return {
        key,
        type: ACTION_CHANGE_DATA,
        value,
    };
};
