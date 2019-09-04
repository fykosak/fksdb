import {
    ACTION_CHANGE_DATA,
    ACTION_SET_INITIAL_DATA,
    ActionChangeData,
    ActionSetInitialData,
} from '../actions/';

export interface InputConnectorItems {
    [key: string]: number;
}

export interface State {
    data: InputConnectorItems;
    initialData: InputConnectorItems;
}

export interface Store {
    inputConnector: State;
}

const setData = (state: State, action: ActionChangeData): State => {
    return {
        ...state,
        data: {
            ...state.data,
            [action.key]: action.value,
        },
    };
};

const setInitialData = (state: State, action: ActionSetInitialData): State => {
    if (action.data) {
        return {
            ...state,
            data: action.data,
            initialData: action.data,
        };
    }
    return state;
};

export const inputConnector = (state: State = {data: {}, initialData: {}}, action): State => {
    switch (action.type) {
        case ACTION_CHANGE_DATA:
            return setData(state, action);
        case ACTION_SET_INITIAL_DATA:
            return setInitialData(state, action);
        default:
            return state;
    }
};
