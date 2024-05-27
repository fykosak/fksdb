import { combineReducers } from 'redux';
import {
    FormStateMap,
    reducer as formReducer
} from 'redux-form';
import {FetchStateMap, fetchReducer} from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/reducer';

export const app = combineReducers({
    fetch: fetchReducer,
    form: formReducer,
});

export interface Store {
    fetch: FetchStateMap;
    form: FormStateMap;
}
