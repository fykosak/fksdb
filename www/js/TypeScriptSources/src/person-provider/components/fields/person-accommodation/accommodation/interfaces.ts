import { IPrice } from '../../../../../shared/components/displays/price/interfaces';

export interface IAccommodationItem {
    accId: number;
    date: string;
    name: string;
    price: IPrice;
}

export interface IPersonAccommodation {
    [date: string]: number;
}
