import { Dispatch } from 'react-redux';
import { reset } from 'redux-form';

import { netteFetch } from '../../shared/helpers/fetch';
import { FORM_NAME } from '../components/form-container';
import { getFullCode } from '../middleware/form';
import { IStore } from '../reducers/index';

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

export const submitStart = (dispatch: Dispatch<IStore>, values: any): Promise<any> => {

    return netteFetch({
        ...values,
        fullCode: getFullCode(values),
    }, (data) => {
        dispatch(submitSuccess(data));
        dispatch(reset(FORM_NAME));
    }, (e) => {
        dispatch(submitFail(e));
    });
};
