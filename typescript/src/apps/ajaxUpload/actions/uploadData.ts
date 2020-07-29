import { dispatchFetch } from '@fetchApi/middleware/netteFetch';
import {
    Action,
    Dispatch,
} from 'redux';
import { Store } from '../reducers';

export const handleSubmit = (dispatch: Dispatch<Action<string>>, accessKey: string, link: string) => {
    return dispatchFetch<{}, any, Store>(link, accessKey, dispatch, JSON.stringify({}));
};
