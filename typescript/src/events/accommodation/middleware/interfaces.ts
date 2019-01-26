import { Price } from '../../../shared/components/displays/price/interfaces';

export interface EventAccommodation {
    eventAccommodationId: number;
    eventId: number;
    capacity: number;
    usedCapacity: number;
    name: string;
    addressId: number;
    price: Price;
    date: string;
}

export interface PersonAccommodation {
    [date: string]: number;
}
