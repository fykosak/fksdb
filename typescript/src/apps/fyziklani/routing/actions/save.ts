import { fetchFail, fetchStart, fetchSuccess } from '@fetchApi/actions/fetch';
import { Request, Response2 } from '@fetchApi/middleware/interfaces';
import {
    Action,
    Dispatch,
} from 'redux';
import { Team } from '../../helpers/interfaces';
import { ResponseData } from '../middleware/interfaces';
import { Store as RoutingStore } from '../reducers/';

export async function dispatchNetteFetch<TFormData, TResponseData, TStore>(
    accessKey: string,
    dispatch: Dispatch<Action<string>>,
    data: Request<TFormData>,
    success: (data: Response2<TResponseData>) => void,
    error: (e: Error | any) => void,
    url: string = null,
): Promise<Response2<TResponseData>> {

    dispatch(fetchStart(accessKey));
    return netteFetch<TFormData, TResponseData>(data, (d: Response2<TResponseData>) => {
            dispatch(fetchSuccess<TResponseData>(d, accessKey));
            success(d);
        },
        (e: Error | any) => {
            dispatch(fetchFail(e, accessKey));
            error(e);
        }, url);
}

export const saveTeams = (accessKey: string, dispatch: Dispatch<Action>, teams: Team[]): Promise<Response2<ResponseData>> => {
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

export async function netteFetch<TFormData, TResponseData>(
    data: Request<TFormData>,
    success: (data: Response2<TResponseData>) => void,
    error: (e: Error | any) => void,
    url: string = null,
): Promise<Response2<TResponseData>> {
    const netteJQuery: any = $;
    return new Promise((resolve: (d: Response2<TResponseData>) => void, reject) => {
        netteJQuery.nette.ajax({
            data,
            error: (e: Error | any) => {
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
