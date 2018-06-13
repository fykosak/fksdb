import { IState as ISubmitStore } from '../fetch-api/reducers/submit';
import {
    IProviderStore,
} from './reducers/provider';

export interface IStore {
    submit: ISubmitStore;
    provider: IProviderStore;
}

export interface IProviderValue<D = any> {
    hasValue: boolean;
    value: D;
}

export interface IResponseData {
    key?: string;
    fields: {
        [value: string]: IProviderValue<any>;
    };
}

export interface IRequestData {
    accessKey: string;
    email: string;
    fields: string[];
}
