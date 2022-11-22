import { Action } from 'redux';

export interface Params {
    cols?: number;
    delay?: number;
    position?: number;
    rows?: number;
    category?: string;
    hardVisible?: boolean;
}

export interface ActionSetParams extends Action<string> {
    data: Params;
}

export const ACTION_SET_PARAMS = '@@game/presentation/ACTION_SET_PARAMS';

export const setParams = (data: Params): ActionSetParams => {
    return {
        data,
        type: ACTION_SET_PARAMS,
    };
};
