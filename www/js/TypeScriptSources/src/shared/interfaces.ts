export interface ITask {
    label: string;
    name: string;
    taskId: number;
}

export interface ISubmit {
    points: number | null;
    taskId: number;
    teamId: number;
    created: any;
}

export interface ISubmits {
    [id: number]: ISubmit;
}

export interface IPlace {
    room: string;
    x: number;
    y: number;
}

export interface IRoom {
    name: string;
    x: number;
    y: number;
    roomId: number;
}

export interface ITeam {
    teamId: number;
    category: string;
    name: string;
    status: string;
    x?: number;
    y?: number;
    roomId?: number;
    room?: string;
}

export interface IUploadDataItem {
    taskId: number;
    deadline: string;
    submitId: number;
    name: string;
    href: string;
}

export interface IUploadData {
    [key: number]: IUploadDataItem;
}

export interface IMessage {
    level: string;
    text: string;
}

export interface IReciveData<D> {
    messages: IMessage[];
    data: D;
}
