import { dispatchNetteFetch } from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/nette-fetch';
import { DataResponse } from 'vendor/fykosak/nette-frontend-component/src/Responses/response';
import { Action, Dispatch } from 'redux';
import { focus, resetSection } from 'redux-form';
import { FORM_NAME } from './spam-person-form';

export interface SpamPersonFormRequest {
    name: string;
}

export const submitStart = async (
        dispatch: Dispatch<Action<string>>,
        values: SpamPersonFormRequest,
        url: string
    ): Promise<DataResponse<SpamPersonFormRequest>> => {
    const data = {
        ...values
    };
    const responseData = await dispatchNetteFetch<SpamPersonFormRequest>(url, dispatch, JSON.stringify(data));
    dispatch(resetSection(FORM_NAME, 'other_name'));
    dispatch(resetSection(FORM_NAME, 'family_name'));
    dispatch(resetSection(FORM_NAME, 'school_label_key'));
    dispatch(focus(FORM_NAME, 'other_name'));
    return responseData;
}
