import { NetteActions } from '@appsCollector/netteActions';

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
