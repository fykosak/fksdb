export interface SubmitModel {
    points: number | null;
    taskId: number;
    teamId: number;
    created: string;
}

export interface Submits {
    [id: number]: SubmitModel;
}
