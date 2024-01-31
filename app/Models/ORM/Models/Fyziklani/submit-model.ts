export interface SubmitModel {
    points: number | null;
    taskId: number;
    teamId: number;
    modified: string;
}

export interface Submits {
    [id: number]: SubmitModel;
}
