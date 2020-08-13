export interface Task {
    label: string;
    name: string;
    taskId: number;
}

export interface Submit {
    points: number | null;
    taskId: number;
    teamId: number;
    created: string;
}

export interface Submits {
    [id: number]: Submit;
}

export interface Place {
    room: string;
    roomId: number;
    x: number;
    y: number;
}

export interface Room {
    name: string;
    x: number;
    y: number;
    roomId: number;
}

export interface Team {
    teamId: number;
    category: string;
    name: string;
    status: string;
    x?: number;
    y?: number;
    roomId?: number;
    room?: string;
    created: string;
}

export interface Event {
    eventId: number;
    year: number;
    eventYear: number;
    begin: string;
    end: string;
    registration_begin: string;
    registration_end: string;
    name: string;
    eventTypeId: number;
}
