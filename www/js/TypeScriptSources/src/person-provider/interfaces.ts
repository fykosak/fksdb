import { IState as ISubmitStore } from '../fetch-api/reducers/submit';
import {
    IProviderStore,
    IProviderValue,
} from './reducers/provider';

export interface IRequestData {
    email: string;
    fields: string[];
}

export interface IStore {
    submit: ISubmitStore;
    provider: IProviderStore;
}

export interface IResponseData {
    key?: string;
    fields: {
        [value: string]: IProviderValue;
    };
}
