import { NetteActions } from '@appsCollector';
import { fetchFail, fetchStart, fetchSuccess } from '@fetchApi/actions/submit';
import { RawResponse,  Response2 } from '@fetchApi/middleware/interfaces';
import jqXHR = JQuery.jqXHR;
import { Action, Dispatch } from 'redux';

export function parseResponse<Data>(rawResponse: RawResponse): Response2<Data> {
    return {
        ...rawResponse,
        actions: new NetteActions(JSON.parse(rawResponse.actions)),
        data: JSON.parse(rawResponse.data),
    };
}

export async function dispatchFetch<ResponseData, TStore, T = any>(
    url: string,
    accessKey: string,
    dispatch: Dispatch<Action<string>>,
    data: FormData | string,
    success: (data: Response2<ResponseData>) => void = () => null,
    error: (e: jqXHR<T>) => void = () => null,
): Promise<any> {
    dispatch(fetchStart(accessKey));
    return fetch(url, {
        body: data,
        method: 'POST',
    })
        .then((response) => response.json())
        .then((d: RawResponse) => {
            const parsedResponse = parseResponse<ResponseData>(d);
            dispatch(fetchSuccess<ResponseData>(parsedResponse, accessKey));
            success(parsedResponse);
        }).catch((e: jqXHR<T>) => {
            dispatch(fetchFail<T>(e, accessKey));
            error(e);
        });
}
