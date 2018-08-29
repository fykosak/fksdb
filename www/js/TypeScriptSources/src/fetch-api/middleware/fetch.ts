import { Dispatch } from 'react-redux';
import {
    submitFail,
    submitStart,
    submitSuccess,
} from '../actions/submit';
import {
    IRequest,
    IResponse,
} from './interfaces';
import jqXHR = JQuery.jqXHR;

export async function netteFetch<F, D, T= any>(
    data: IRequest<F>,
    success: (data: IResponse<D>) => void,
    error: (e: jqXHR<T>) => void,
): Promise<IResponse<D>> {
    const netteJQuery: any = $;
    return new Promise((resolve: (d: IResponse<D>) => void, reject) => {
        netteJQuery.nette.ajax({
            data,
            error: (e: jqXHR<T>) => {
                error(e);
                reject(e);
            },
            method: 'POST',
            success: (d: IResponse<D>) => {
                success(d);
                resolve(d);
            },
        });
    });
}

export async function uploadFile<F, D, T>(
    data: IRequest<F>,
    success: (data: IResponse<D>) => void,
    error: (e: jqXHR<T>) => void,
): Promise<IResponse<D>> {
    return new Promise((resolve: (d: IResponse<D>) => void, reject) => {
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
            success: (d: IResponse<D>) => {
                resolve(d);
                success(d);
            },
            type: 'POST',
            url: '#',
        });
    });
}

export async function dispatchNetteFetch<TFormData, TResponseData, TStore, T= any>(
    accessKey: string,
    dispatch: Dispatch<TStore>,
    data: IRequest<TFormData>,
    success: (data: IResponse<TResponseData>) => void,
    error: (e: jqXHR<T>) => void,
): Promise<IResponse<TResponseData>> {

    dispatch(submitStart(accessKey));
    return netteFetch<TFormData, TResponseData, T>(data, (d: IResponse<TResponseData>) => {
            dispatch(submitSuccess<TResponseData>(d, accessKey));
            success(d);
        },
        (e: jqXHR<T>) => {
            dispatch(submitFail<T>(e, accessKey));
            error(e);
        });
}

export async function dispatchUploadFile<TFormData, TResponseData, TStore, T= any>(
    accessKey: string,
    dispatch: Dispatch<TStore>,
    data: IRequest<TFormData>,
    success: (data: IResponse<TResponseData>) => void,
    error: (e: jqXHR<T>) => void,
): Promise<IResponse<TResponseData>> {

    dispatch(submitStart(accessKey));
    return uploadFile<TFormData, TResponseData, T>(data, (d: IResponse<TResponseData>) => {
            dispatch(submitSuccess<TResponseData>(d, accessKey));
            success(d);
        },
        (e: jqXHR<T>) => {
            dispatch(submitFail<T>(e, accessKey));
            error(e);
        });
}
