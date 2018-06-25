import { IPrice } from '../../shared/components/displays/price/interfaces';
import { IPersonSelector } from './price';

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

export interface IPersonDefinition {
    personSelector: IPersonSelector;
    index: number;
    type: "participant" | "teacher";
}
