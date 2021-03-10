import { ModelFyziklaniSubmit } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniSubmit';

export interface Submits {
    [id: number]: ModelFyziklaniSubmit;
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
