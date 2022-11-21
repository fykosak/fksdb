import { dispatchNetteFetch } from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/netteFetch';
import { DataResponse } from 'vendor/fykosak/nette-frontend-component/src/Responses/response';
import {
    Action,
    Dispatch,
} from 'redux';
import { reset } from 'redux-form';
import { FORM_NAME } from './Components/FOFForm';
import { getFullCode } from './middleware';

export interface SubmitFormRequest {
    code: string;
    points: number;
}

export const submitStart = async (dispatch: Dispatch<Action<string>>, values: SubmitFormRequest, url): Promise<DataResponse<SubmitFormRequest>> => {
    const data = {
        ...values,
        code: getFullCode(values.code),
    };
    const responseData = await dispatchNetteFetch<SubmitFormRequest>(url, dispatch, JSON.stringify(data));
    dispatch(reset(FORM_NAME))
    return responseData;
}
