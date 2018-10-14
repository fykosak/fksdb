import {
    ACTION_CHANGE_DATA,
    ACTION_SET_INITIAL_DATA,
} from '../actions/';

export interface IInputConnectorItems {
    [key: string]: number;
}

export interface IInputConnectorState {
    data: IInputConnectorItems;
}

export interface IInputConnectorStore {
    inputConnector: IInputConnectorState;
}

const setData = (state: IInputConnectorState, action): IInputConnectorState => {
    return {
        ...state,
        [action.key]: action.value,
    };
};

const setInitialData = (state: IInputConnectorState, action): IInputConnectorState => {
    if (action.data) {
        return action.data;
    }
    return state;
};

export const inputConnector = (state: IInputConnectorState = {data: {}}, action): IInputConnectorState => {
    switch (action.type) {
        case ACTION_CHANGE_DATA:
            return setData(state, action);
        case ACTION_SET_INITIAL_DATA:
            return setInitialData(state, action);
        default:
            return state;
    }
};
