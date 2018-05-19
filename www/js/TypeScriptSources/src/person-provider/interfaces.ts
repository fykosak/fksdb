import { IState as ISubmitStore } from '../shared/reducers/submit';
import {
    IProviderStore,
    IProviderValue,
} from './reducers/provider';

export interface IResponseValues {
    act: string;
    email: string;
    fields: string[];
}

export interface IStore {
    submit: ISubmitStore;
    provider: IProviderStore;
}

export interface IReceiveProviderData {
    fields: {
        [value: string]: IProviderValue;
    };
}
