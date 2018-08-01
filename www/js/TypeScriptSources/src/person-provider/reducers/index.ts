import { combineReducers } from 'redux';
import {
    IState as ISubmitStore,
    submit,
} from '../../fetch-api/reducers/submit';
import {
    form,
    IFormState,
} from './form';

import {
    externalForm,
    IExternalHtmlState,
} from './external-form';

export const app = combineReducers({
    externalForm,
    form,
    submit,
});

export interface IStore {
    externalForm: IExternalHtmlState;
    form: IFormState;
    submit: ISubmitStore;
}
