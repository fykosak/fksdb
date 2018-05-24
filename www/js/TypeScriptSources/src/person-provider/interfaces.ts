import { IState as ISubmitStore } from '../shared/reducers/submit';
import {
    IProviderStore,
    IProviderValue,
} from './reducers/provider';
import { IReceiveData } from '../shared/interfaces';

export interface IResponseValues {
    act: string;
    email: string;
    fields: string[];
}

export interface IStore {
    submit: ISubmitStore;
    provider: IProviderStore;
}

export interface IReceiveProviderData<D> extends IReceiveData<D> {
    key: string;
}

export interface IReceiveProviderFields {
    fields: {
        [value: string]: IProviderValue;
    };
}
