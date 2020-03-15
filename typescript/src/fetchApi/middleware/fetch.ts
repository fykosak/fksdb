import {
    Action,
    Dispatch,
} from 'redux';
import {
    submitFail,
    submitStart,
    submitSuccess,
} from '../actions/submit';
import {
    Request,
    Response,
} from './interfaces';
import jqXHR = JQuery.jqXHR;

export async function netteFetch<TFormData, TResponseData, T = any>(
    data: Request<TFormData>,
    success: (data: Response<TResponseData>) => void,
    error: (e: jqXHR<T>) => void,
    url: string = null,
): Promise<Response<TResponseData>> {
    const netteJQuery: any = $;
    return new Promise((resolve: (d: Response<TResponseData>) => void, reject) => {
        netteJQuery.nette.ajax({
            data,
            error: (e: jqXHR<T>) => {
                error(e);
                reject(e);
            },
            method: 'POST',
            success: (d: Response<TResponseData>) => {
                success(d);
                resolve(d);
            },
            url,
        });
    });
}

export async function uploadFile<F, D, T>(
    data: Request<F> | FormData,
    success: (data: Response<D>) => void,
    error: (e: jqXHR<T>) => void,
    url: string = null,
): Promise<Response<D>> {
    return new Promise((resolve: (d: Response<D>) => void, reject) => {
        $.ajax({
            cache: false,
            contentType: false,
            data,
            dataType: 'json',
            error: (e: jqXHR<T>) => {
                reject(e);
                error(e);
            },
            processData: false,
            success: (d: Response<D>) => {
                resolve(d);
                success(d);
            },
            type: 'POST',
            url,
        });
    });
}

export async function dispatchNetteFetch<TFormData, TResponseData, TStore, T = any>(
    accessKey: string,
    dispatch: Dispatch<Action<string>>,
    data: Request<TFormData>,
    success: (data: Response<TResponseData>) => void,
    error: (e: jqXHR<T>) => void,
    url: string = null,
): Promise<Response<TResponseData>> {

    dispatch(submitStart(accessKey));
    return netteFetch<TFormData, TResponseData, T>(data, (d: Response<TResponseData>) => {
            dispatch(submitSuccess<TResponseData>(d, accessKey));
            success(d);
        },
        (e: jqXHR<T>) => {
            dispatch(submitFail<T>(e, accessKey));
            error(e);
        }, url);
}

export async function dispatchUploadFile<TFormData, TResponseData, TStore, T = any>(
    accessKey: string,
    dispatch: Dispatch<Action<string>>,
    data: Request<TFormData> | FormData,
    success: (data: Response<TResponseData>) => void,
    error: (e: jqXHR<T>) => void,
    url: string = null,
): Promise<Response<TResponseData>> {

    dispatch(submitStart(accessKey));
    return uploadFile<TFormData, TResponseData, T>(data, (d: Response<TResponseData>) => {
            dispatch(submitSuccess<TResponseData>(d, accessKey));
            success(d);
        },
        (e: jqXHR<T>) => {
            dispatch(submitFail<T>(e, accessKey));
            error(e);
        }, url);
}
