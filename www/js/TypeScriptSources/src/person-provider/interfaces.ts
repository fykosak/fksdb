import { IState as ISubmitStore } from '../submit/reducers/submit';
import {
    IProviderStore,
    IProviderValue,
} from './reducers/provider';
import { IResponse } from '../submit/middleware/interfaces';

export interface IResponseValues {
    act: string;
    email: string;
    fields: string[];
}

export interface IStore {
    submit: ISubmitStore;
    provider: IProviderStore;
}

export interface IReceiveProviderData<D> extends IResponse<D> {
    key: string;
}

export interface IReceiveProviderFields {
    fields: {
        [value: string]: IProviderValue;
    };
}
