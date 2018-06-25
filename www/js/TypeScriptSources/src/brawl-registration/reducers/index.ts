import { combineReducers } from 'redux';

import { reducer as formReducer } from 'redux-form';
import {
    IState as ISubmitStore,
    submit,
} from '../../fetch-api/reducers/submit';
import {
    ILangStore,
    lang,
} from '../../lang/reducers';
import {
    IProviderStore,
    provider,
} from '../../person-provider/reducers/provider';
import { IDefinitionsState } from '../../shared/definitions/interfaces';
import { definitions } from '../../shared/definitions/reducers/definitions';

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
