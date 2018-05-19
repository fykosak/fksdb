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
}

export interface IAccommodationItem {
    accId: number;
    date: string;
    name: string;
    price: IPrice;
}

export interface IPersonDefinition {
    fields: string[];
    index: number;
    type: "participant" | "teacher";
}
