import { Dispatch } from 'react-redux';
import { IStore } from '../reducers/index';
/**
 * Created by miso on 14.10.2017.
 */
export const ACTION_SET_CONTROL_CODE = 'ACTION_SET_CONTROL_CODE';

export const setControlCode = (code: number, valid: boolean) => {
    return {
        code,
        type: ACTION_SET_CONTROL_CODE,
        valid,
    };
};

export const ACTION_CLEAR_INPUTS = 'ACTION_CLEAR_INPUTS';

export const clearInputs = () => {
    return {
        type: ACTION_CLEAR_INPUTS,
    };
};

export const ACTION_SET_TASK_CODE = 'ACTION_SET_TASK_CODE';

export const setTaskCode = (code, valid) => {
    return {
        code,
        type: ACTION_SET_TASK_CODE,
        valid,
    };
};

export const ACTION_SET_TEAM_CODE = 'ACTION_SET_TEAM_CODE';

export const setTeamCode = (code, valid) => {
    return {
        code,
        type: ACTION_SET_TEAM_CODE,
        valid,
    };
};

export const ACTION_SET_TASK_INPUT = 'ACTION_SET_TASK_INPUT';
export const setTaskInput = (node: HTMLInputElement) => {
    return {
        input: node,
        type: ACTION_SET_TASK_INPUT,
    };
};
export const ACTION_SET_TEAM_INPUT = 'ACTION_SET_TEAM_INPUT';
export const setTeamInput = (node: HTMLInputElement) => {
    return {
        input: node,
        type: ACTION_SET_TEAM_INPUT,
    };
};
export const ACTION_SET_CONTROL_INPUT = 'ACTION_SET_CONTROL_INPUT';
export const setControlInput = (node: HTMLInputElement) => {
    return {
        input: node,
        type: ACTION_SET_CONTROL_INPUT,
    };
};

export const ACTION_SUBMIT_START = 'ACTION_SUBMIT_START';

export const ACTION_SUBMIT_SUCCESS = 'ACTION_SUBMIT_SUCCESS';
const submitSuccess = () => {
    return {
        type: ACTION_SUBMIT_SUCCESS,
    };
};

export const ACTION_SUBMIT_FAIL = 'ACTION_SUBMIT_FAIL';
const submitFail = (error) => {
    return {
        error,
        type: ACTION_SUBMIT_FAIL,
    };
};

export const submitStart = (dispatch: Dispatch<IStore>, points: number) => {

    const data: { points?: number } = {};
    data.points = points;
    const netteJQuery: any = $;
    netteJQuery.nette.ajax({
        data,
        error: (e) => {
            dispatch(submitFail(e));
        },
        success: (data) => {

            dispatch(submitSuccess());
            dispatch(clearInputs());
        },
    });

    return {
        points,
        type: ACTION_SUBMIT_START,
    };
};
