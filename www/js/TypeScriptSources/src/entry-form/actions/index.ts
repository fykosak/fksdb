import { Dispatch } from 'react-redux';
import { reset } from 'redux-form';
import { dispatchNetteFetch } from '../../fetch-api/middleware/fetch';
import { FORM_NAME } from '../components/form-container';
import { getFullCode } from '../middleware/form';
import { IStore } from '../reducers/';

export const submitStart = (dispatch: Dispatch<IStore>, values: any): Promise<any> => {

    return dispatchNetteFetch<any, any, any>('brawl-entry-form', dispatch, {
        ...values,
        fullCode: getFullCode(values),
    }, () => {
        dispatch(reset(FORM_NAME));
    });
};
