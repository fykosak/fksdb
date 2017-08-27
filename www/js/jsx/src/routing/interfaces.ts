export interface IPlace {
    room: string;
    x: number;
    y: number;
}

export interface ITeam {
    teamID: number;
    category: string;
    name: string;
    x?: number;
    y?: number;
    room?: string;
}

export interface IRoom {
    name: string;
    x: number;
    y: number;
}
