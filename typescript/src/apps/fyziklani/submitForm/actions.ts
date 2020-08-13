import { dispatchFetch } from '@fetchApi/netteFetch';
import {
    Action,
    Dispatch,
} from 'redux';
import { reset } from 'redux-form';
import { FORM_NAME } from './components/container';
import { getFullCode } from './middleware';

export interface SubmitFormRequest {
    code: string;
    points: number;
}

export const ACCESS_KEY = '@fyziklani-submit-form';

export const submitStart = (dispatch: Dispatch<Action<string>>, values: SubmitFormRequest, url): Promise<any> => {
    const data = {
        ...values,
        code: getFullCode(values.code),
    };
    return dispatchFetch<SubmitFormRequest>(url, ACCESS_KEY, dispatch, JSON.stringify(data), () => {
        dispatch(reset(FORM_NAME));
    });
};
