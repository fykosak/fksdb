import jqXHR = JQuery.jqXHR;
import { NetteActions } from '@appsCollector';
import { Action } from 'redux';

export interface Message {
    level: string;
    text: string;
}

export interface Request<F> {
    requestData: F;
    act: string;
}

export interface RawResponse {
    actions: string;
    data: string;
    messages: Message[];
}

export interface Response2<D> {
    actions: NetteActions;
    data: D;
    messages: Message[];
}

export interface ActionFetch extends Action {
    accessKey: string;
}

export interface ActionFetchFail<T = any> extends ActionFetch {
    error: jqXHR<T>;
}

export interface ActionFetchSuccess<D> extends ActionFetch {
    data: D;
}
