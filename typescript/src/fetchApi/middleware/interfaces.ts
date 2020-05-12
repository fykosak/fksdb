import { Action } from 'redux';
import jqXHR = JQuery.jqXHR;

export interface Message {
    level: string;
    text: string;
}

export interface Request<F> {
    requestData: F;
    act: string;
}

export interface Response<D> {
    act: string;
    messages: Message[];
    responseData: D;
}

export interface ActionSubmit extends Action {
    accessKey: string;
}

export interface ActionSubmitFail<T = any> extends ActionSubmit {
    error: jqXHR<T>;
}

export interface ActionSubmitSuccess<D> extends ActionSubmit {
    data: Response<D>;
}
