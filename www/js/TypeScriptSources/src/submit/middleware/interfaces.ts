export interface IMessage {
    level: string;
    text: string;
}

export interface IRequest {
    act: string;
}

export interface IResponse<D> {
    act: string;
    messages: IMessage[];
    data: D;
}
