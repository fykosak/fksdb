import { IPrice } from '../../../../../brawl-registration/middleware/price';

export interface IAccommodationItem {
    accId: number;
    date: string;
    name: string;
    price: IPrice;
}

export interface IPersonAccommodation {
    [date: string]: number;
}
