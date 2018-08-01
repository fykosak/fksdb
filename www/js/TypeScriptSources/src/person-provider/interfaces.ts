import { IState as ISubmitStore } from '../fetch-api/reducers/submit';



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
