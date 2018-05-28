import { combineReducers } from 'redux';

import { reducer as formReducer } from 'redux-form';
import {
    ILangStore,
    lang,
} from '../../lang/reducers';
import {
    IProviderStore,
    provider,
} from '../../person-provider/reducers/provider';
import {
    IState as ISubmitStore,
    submit,
} from '../../submit/reducers/submit';
import {
    definitions,
    IDefinitionsState,
} from './definitions';

export const app = combineReducers({
    definitions,
    form: formReducer,
    lang,
    provider,
    submit,
});

export interface IStore {
    definitions: IDefinitionsState;
    form: typeof formReducer;
    submit: ISubmitStore;
    provider: IProviderStore;
    lang: ILangStore;
}
