import { IEventAccommodation } from '../../events/accommodation/middleware/interfaces';

export interface IPaymentAccommodationItem {
    label: string;
    id: number;
    accommodation: IEventAccommodation;
    hasPayment: boolean;
}
