export interface ISubmit {
    points: number|null;
    task_id: number;
    team_id: number;
    created: any;
}

export interface ITeam {
    category: string;
    name: string;
    room?: string;
    team_id: number;
}

export interface ITask {
    label: string;
    task_id: number;
}
