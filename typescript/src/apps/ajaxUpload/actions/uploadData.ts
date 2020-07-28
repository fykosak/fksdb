import { dispatchNetteFetch2 } from '@fetchApi/middleware/fetch';
import {
    Action,
    Dispatch,
} from 'redux';
import { Store } from '../reducers';

export const handleSubmit = (dispatch: Dispatch<Action<string>>, accessKey: string, submitId: number, link: string) => {
    return dispatchNetteFetch2<{ submitId: number }, any, Store>(link, accessKey, dispatch, {submitId});
};
