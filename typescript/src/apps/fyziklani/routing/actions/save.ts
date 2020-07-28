import { submitFail, submitStart, submitSuccess } from '@fetchApi/actions/submit';
import { Request, Response } from '@fetchApi/middleware/interfaces';
import {
    Action,
    Dispatch,
} from 'redux';
import { Team } from '../../helpers/interfaces';
import { ResponseData } from '../middleware/interfaces';
import { Store as RoutingStore } from '../reducers/';
import jqXHR = JQuery.jqXHR;

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

export const saveTeams = (accessKey: string, dispatch: Dispatch<Action>, teams: Team[]): Promise<Response<ResponseData>> => {
    const data = {act: 'routing-save', requestData: teams};
    return dispatchNetteFetch<Team[], ResponseData, RoutingStore>
    (accessKey, dispatch, data, () => null, () => {
        setTimeout(() => {
            dispatch(removeUpdatesTeams());
        }, 5000);
    });
};

export const ACTION_REMOVE_UPDATED_TEAMS = 'ACTION_REMOVE_UPDATED_TEAMS';

const removeUpdatesTeams = (): Action => {
    return {
        type: ACTION_REMOVE_UPDATED_TEAMS,
    };
};

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
