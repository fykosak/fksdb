import { dispatchNetteFetch } from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/nette-fetch';
import { DataResponse } from 'vendor/fykosak/nette-frontend-component/src/Responses/response';
import { Action, Dispatch } from 'redux';
import { focus, reset } from 'redux-form';
import { FORM_NAME } from './Components/main-form';

export interface SubmitFormRequest {
    code: string;
    points: number;
}

export const submitStart = async (dispatch: Dispatch<Action<string>>, values: SubmitFormRequest, url): Promise<DataResponse<SubmitFormRequest>> => {
    const data = {
        ...values,
        code: values.code,
    };
    const responseData = await dispatchNetteFetch<SubmitFormRequest>(url, dispatch, JSON.stringify(data));
    await dispatch(reset(FORM_NAME));
    await dispatch(focus(FORM_NAME, 'code'))
    return responseData;
}
