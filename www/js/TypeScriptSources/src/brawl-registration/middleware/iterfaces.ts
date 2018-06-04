import { IPrice } from './price';

export interface IScheduleItem {
    date: string;
    description: string;
    id: number;
    scheduleName: string;
    price?: IPrice;
    time: {
        begin: string;
        end: string;
    };
    requireIdNumber: boolean;
}

export interface IAccommodationItem {
    accId: number;
    date: string;
    name: string;
    price: IPrice;
}

export interface IPersonDefinition {
    index: number;
    type: "participant" | "teacher";
}
