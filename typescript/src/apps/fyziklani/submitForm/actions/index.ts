import { fetchFail, fetchStart, fetchSuccess } from '@fetchApi/actions/submit';
import { Request, Response2 } from '@fetchApi/middleware/interfaces';
import {
    Action,
    Dispatch,
} from 'redux';
import { reset } from 'redux-form';
import { FORM_NAME } from '../components/formContainer';
import { getFullCode } from '../middleware/form';
import { Store as SubmitStore } from '../reducers';
import jqXHR = JQuery.jqXHR;

export interface SubmitFormRequest {
    code: string;
    points: number;
}

export const ACCESS_KEY = '@fyziklani-submit-form';

export const submitStart = (dispatch: Dispatch<Action<string>>, values: SubmitFormRequest, url): Promise<Response2<void>> => {
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

async function dispatchNetteFetch2<TFormData, TResponseData, TStore, T = any>(
    url: string,
    accessKey: string,
    dispatch: Dispatch<Action<string>>,
    data: TFormData,
    success: (data: Response2<TResponseData>) => void = () => null,
    error: (e: jqXHR<T>) => void = () => null,
): Promise<Response2<TResponseData>> {

    dispatch(fetchStart(accessKey));
    return netteFetch2<TFormData, TResponseData, T>(data, (d: Response2<TResponseData>) => {
            dispatch(fetchSuccess<TResponseData>(d, accessKey));
            success(d);
        },
        (e: jqXHR<T>) => {
            dispatch(fetchFail<T>(e, accessKey));
            error(e);
        }, url);
}

async function netteFetch2<TRequestData, TResponseData, T = any>(
    data: TRequestData,
    success: (data: Response2<TResponseData>) => void,
    error: (e: jqXHR<T>) => void,
    url: string = null,
): Promise<Response2<TResponseData>> {
    const netteJQuery: any = $;
    return new Promise((resolve: (d: Response2<TResponseData>) => void, reject) => {
        netteJQuery.nette.ajax({
            data,
            error: (e: jqXHR<T>) => {
                error(e);
                reject(e);
            },
            method: 'POST',
            success: (d: Response2<TResponseData>) => {
                success(d);
                resolve(d);
            },
            url,
        });
    });
}
