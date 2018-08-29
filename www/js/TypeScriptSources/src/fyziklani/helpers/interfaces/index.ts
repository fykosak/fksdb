export interface ITask {
    label: string;
    name: string;
    taskId: number;
}

export interface ISubmit {
    points: number | null;
    taskId: number;
    teamId: number;
    created: string;
}

export interface ISubmits {
    [id: number]: ISubmit;
}

export interface IPlace {
    room: string;
    roomId: number;
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
