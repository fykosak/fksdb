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

export async function netteFetch<F, D>(data: IRequest<F>, success: (data: IResponse<D>) => void = null, error: (e) => void = null): Promise<IResponse<D>> {
    const netteJQuery: any = $;
    return new Promise((resolve: (d: IResponse<D>) => void, reject) => {
        netteJQuery.nette.ajax({
            data,
            error: (e) => {
                if (error) {
                    error(e);
                }
                reject(e);
            },
            method: 'POST',
            success: (d: IResponse<D>) => {
                if (success) {
                    success(d);
                }
                resolve(d);
            },
        });
    });
}

export async function uploadFile<F, D>(data: IRequest<F>, success: (data: IResponse<D>) => void, error: (e: any) => void): Promise<IResponse<D>> {
    return new Promise((resolve: (d: IResponse<D>) => void, reject) => {
        $.ajax({
            cache: false,
            contentType: false,
            data,
            dataType: 'json',
            error: (e) => {
                reject(e);
                error(e);
            },
            processData: false,
            success: (d: IResponse<D>) => {
                resolve(d);
                if (success) {
                    success(d);
                }
            },
            type: 'POST',
            url: '#',
        });
    });
}

export async function dispatchNetteFetch<F, D, S>(accessKey: string, dispatch: Dispatch<S>, data: IRequest<F>, success: (data: IResponse<D>) => void = null, error: (e) => void = null): Promise<IResponse<D>> {
    dispatch(submitStart(accessKey));
    return netteFetch<F, D>(data, (d: IResponse<D>) => {
            dispatch(submitSuccess<D>(d, accessKey));
            if (success) {
                success(d);
            }
        },
        (e) => {
            dispatch(submitFail(e, accessKey));
            if (error) {
                error(e);
            }
        });
}

export async function dispatchUploadFile<F, D, S>(accessKey: string, dispatch: Dispatch<S>, data: IRequest<F>, success: (data: IResponse<D>) => void, error: (e: any) => void): Promise<IResponse<D>> {
    dispatch(submitStart(accessKey));
    return uploadFile<F, D>(data, (d: IResponse<D>) => {
            dispatch(submitSuccess<D>(d, accessKey));
            success(d);
        },
        (e) => {
            dispatch(submitFail(e, accessKey));
            error(e);
        });
}
