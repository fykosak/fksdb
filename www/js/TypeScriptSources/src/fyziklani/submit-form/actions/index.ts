import { Dispatch } from 'react-redux';
import { reset } from 'redux-form';
import { dispatchNetteFetch } from '../../../fetch-api/middleware/fetch';
import { IResponse } from '../../../fetch-api/middleware/interfaces';
import { FORM_NAME } from '../components/form-container';
import { getFullCode } from '../middleware/form';
import { IFyziklaniSubmitStore } from '../reducers';

interface ISubmitFormRequest {
    code: string;
    points: number;
}

export const submitStart = (dispatch: Dispatch<IFyziklaniSubmitStore>, values: ISubmitFormRequest): Promise<IResponse<void>> => {
    const data = {
        act: 'submit',
        requestData: {
            ...values,
            code: getFullCode(values.code),
        },
    };
    return dispatchNetteFetch<ISubmitFormRequest, void, IFyziklaniSubmitStore>('@@fyziklani-submit', dispatch, data, () => {
        dispatch(reset(FORM_NAME));
    }, () => null);
};
