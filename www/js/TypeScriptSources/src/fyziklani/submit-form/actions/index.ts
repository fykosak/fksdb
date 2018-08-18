import { Dispatch } from 'react-redux';
import { reset } from 'redux-form';
import { dispatchNetteFetch } from '../../../fetch-api/middleware/fetch';
import { FORM_NAME } from '../components/form-container';
import { getFullCode } from '../middleware/form';
import { IFyziklaniSubmitStore } from '../reducers';

export const submitStart = (dispatch: Dispatch<IFyziklaniSubmitStore>, values: any): Promise<any> => {
    const data = {act: 'submit', data: getFullCode(values)};
    return dispatchNetteFetch<string, any, IFyziklaniSubmitStore>('@@fyziklani-submit', dispatch, data, () => {
        dispatch(reset(FORM_NAME));
    }, () => null);
};
