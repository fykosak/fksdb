import { IAccommodationItem } from '../components/accommodation';

export interface IPrice {
    eur: number;
    kc: number;
}

export const getPrice = (accommodationDef: IAccommodationItem[], acc): IPrice => {

    const sum = {
        eur: 0,
        kc: 0,
    };

    if (!acc) {
        return sum;
    }

    for (const date in acc) {
        if (acc.hasOwnProperty(date)) {
            const selectedAcc = accommodationDef.filter((value) => {
                return value.accId === acc[date];
            })[0];
            if (selectedAcc) {
                sum.eur += +selectedAcc.price.eur;
                sum.kc += +selectedAcc.price.kc;
            }
        }
    }
    return sum;
};
