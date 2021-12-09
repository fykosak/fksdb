import { fetchReducer, FetchStateMap } from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/reducer';
import { combineReducers } from 'redux';
import {
    FormStateMap,
    reducer as formReducer,
} from 'redux-form';

export const app = combineReducers<Store>({
    fetch: fetchReducer,
    form: formReducer,
});

export interface Store {
    fetch: FetchStateMap;
    form: FormStateMap;
}
