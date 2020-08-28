import { NetteActions } from '@appsCollector/netteActions';
import { fetchFail, fetchStart, fetchSuccess } from '@fetchApi/actions';
import { RawResponse, Response2 } from '@fetchApi/interfaces';
import { Action, Dispatch } from 'redux';

export function parseResponse<Data>(rawResponse: RawResponse): Response2<Data> {
    return {
        ...rawResponse,
        actions: new NetteActions(JSON.parse(rawResponse.actions)),
        data: JSON.parse(rawResponse.data),
    };
}

export async function dispatchFetch<ResponseData>(
    url: string,
    accessKey: string,
    dispatch: Dispatch<Action<string>>,
    data: BodyInit | null,
    success: (data: Response2<ResponseData>) => void = () => null,
    error: (e: Error | any) => void = () => null,
): Promise<Response2<ResponseData> | void> {
    dispatch(fetchStart(accessKey));
    return fetch(url, {
        body: data,
        method: 'POST',
    })
        .then((response) => {
            if (response.redirected) {
                window.location.href = response.url;
                throw new Error();
            }
            return response.json();
        })
        .then((d: RawResponse) => {
            const parsedResponse = parseResponse<ResponseData>(d);
            dispatch(fetchSuccess<ResponseData>(parsedResponse, accessKey));
            success(parsedResponse);
            return parsedResponse;
        }).catch((e: Error | any) => {
            dispatch(fetchFail(e, accessKey));
            error(e);
        });
}
