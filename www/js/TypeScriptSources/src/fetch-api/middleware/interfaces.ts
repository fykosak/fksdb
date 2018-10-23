import { Action } from 'redux';
import jqXHR = JQuery.jqXHR;

export interface IMessage {
    level: string;
    text: string;
}

export interface IRequest<F> {
    requestData: F;
    act: string;
}

export interface IResponse<D> {
    act: string;
    messages: IMessage[];
    responseData: D;
}

export interface IActionSubmit extends Action {
    accessKey: string;
}

export interface IActionSubmitFail<T = any> extends IActionSubmit {
    error: jqXHR<T>;
}

export interface IActionSubmitSuccess<D> extends IActionSubmit {
    data: IResponse<D>;
}
