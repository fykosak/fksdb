import { IPrice } from '../../shared/components/displays/price/interfaces';

export interface IAccommodationItem {
    eventAccommodationId: number;
    eventId: number;
    capacity: number;
    usedCapacity: number;
    name: string;
    addressId: number;
    price: IPrice;
    date: string;
}

export interface IPersonAccommodation {
    [date: string]: number;
}
