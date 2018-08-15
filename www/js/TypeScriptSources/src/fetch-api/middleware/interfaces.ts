import { Action } from 'redux';

export interface IMessage {
    level: string;
    text: string;
}

export interface IRequest<F> {
    data: F;
    act: string;
}

export interface IResponse<D> {
    act: string;
    messages: IMessage[];
    data: D;
}

export interface ISubmitAction extends Action {
    accessKey: string;
}

export interface ISubmitFailAction extends ISubmitAction {
    error: Error;
}

export interface ISubmitSuccessAction<D> extends ISubmitAction {
    data: IResponse<D>;
}
