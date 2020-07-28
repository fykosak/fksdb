import { dispatchNetteFetch2 } from '@fetchApi/middleware/fetch';
import { Request, Response } from '@fetchApi/middleware/interfaces';
import {
    Action,
    Dispatch,
} from 'redux';
import { reset } from 'redux-form';
import { FORM_NAME } from '../components/formContainer';
import { getFullCode } from '../middleware/form';
import { Store as SubmitStore } from '../reducers';

export interface SubmitFormRequest {
    code: string;
    points: number;
}

export const ACCESS_KEY = '@fyziklani-submit-form';

export const submitStart = (dispatch: Dispatch<Action<string>>, values: SubmitFormRequest, url): Promise<Response<void>> => {
    const data = {
        act: 'submit',
        requestData: {
            ...values,
            code: getFullCode(values.code),
        },
    };
    return dispatchNetteFetch2<Request<SubmitFormRequest>, void, SubmitStore>(url, ACCESS_KEY, dispatch, data, () => {
        dispatch(reset(FORM_NAME));
    });
};
