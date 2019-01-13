import {
    Action,
    Dispatch,
} from 'redux';
import { reset } from 'redux-form';
import { dispatchNetteFetch } from '../../../fetch-api/middleware/fetch';
import { Response } from '../../../fetch-api/middleware/interfaces';
import { FORM_NAME } from '../components/form-container';
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
    return dispatchNetteFetch<SubmitFormRequest, void, SubmitStore>(ACCESS_KEY, dispatch, data, () => {
        dispatch(reset(FORM_NAME));
    }, () => null, url);
};
