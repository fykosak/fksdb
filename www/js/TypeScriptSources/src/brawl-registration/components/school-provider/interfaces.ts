export interface ISendValues {
    act: string;
    payload: string;
}

interface ISchool {
    id: number;
    name: string;
}

export interface IRecieveValues {
    data: ISchool[];
}
