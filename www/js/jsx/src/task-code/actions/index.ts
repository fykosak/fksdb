import { Dispatch } from 'react-redux';
import { IStore } from '../reducers/index';
import { reset } from 'redux-form';
import { FORM_NAME } from '../components/inputs-container';
import { getFullCode } from '../middleware/form';

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
const submitSuccess = (data) => {
    return {
        data,
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

export const submitStart = (dispatch: Dispatch<IStore>, values: any) => {
    return new Promise((resolve, reject) => {
        const netteJQuery: any = $;

        netteJQuery.nette.ajax({
            data: {
                ...values,
                fullCode: getFullCode(values),
            },
            error: (e) => {
                dispatch(submitFail(e));
                reject();
            },

            // requires form name
            success: (data) => {
                dispatch(submitSuccess(data));
                dispatch(reset(FORM_NAME));
                resolve();
            },
        });
    });

};
