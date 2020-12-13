import { dispatchFetch } from '@FKSDB/Model/FrontEnd/Fetch/netteFetch';
import {
    Action,
    Dispatch,
} from 'redux';
import { reset } from 'redux-form';
import { FORM_NAME } from './Components/Container';
import { getFullCode } from './middleware';

export interface SubmitFormRequest {
    code: string;
    points: number;
}

export const submitStart = (dispatch: Dispatch<Action<string>>, values: SubmitFormRequest, url): Promise<any> => {
    const data = {
        ...values,
        code: getFullCode(values.code),
    };
    return dispatchFetch<SubmitFormRequest>(url, dispatch, JSON.stringify(data), () => {
        dispatch(reset(FORM_NAME));
    });
};
