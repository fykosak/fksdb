import * as React from 'react';
import {
    Field,
} from 'redux-form';
import Row from './row';

import Price from './price';

export interface IAccommodationItem {
    accId: number;
    date: string;
    name: string;
    price: {
        eur: number;
        kc: number;
    };
}

export const accommodationDef: IAccommodationItem[] = [
    {
        accId: 1,
        date: '2017-05-02',
        name: 'Elf',
        price: {
            eur: 10,
            kc: 300,
        },
    },
    {
        accId: 2,
        date: '2017-05-03',
        name: 'Elf',
        price: {
            eur: 10,
            kc: 300,
        },
    },
    {
        accId: 3,
        date: '2017-05-04',
        name: 'Elf',
        price: {
            eur: 10,
            kc: 300,
        },
    },
    {
        accId: 4,
        date: '2017-05-05',
        name: 'Elf',
        price: {
            eur: 10,
            kc: 300,
        },
    },
    {
        accId: 5,
        date: '2017-05-03',
        name: 'Duo',
        price: {
            eur: 20,
            kc: 500,
        },
    },
    {
        accId: 6,
        date: '2017-05-04',
        name: 'Duo',
        price: {
            eur: 20,
            kc: 500,
        },
    },
    {
        accId: 7,
        date: '2017-05-05',
        name: 'Duo',
        price: {
            eur: 20,
            kc: 500,
        },
    },
];

interface IProps {
    type: string;
    index: number;
}

export default class Accommodation extends React.Component<IProps, {}> {

    public render() {
        const dates = {};
        const names = [];
        accommodationDef.forEach((value) => {
            if (names.indexOf(value.name) === -1) {
                names.push(value.name);
            }
            dates[value.date] = dates[value.date] || [];
            dates[value.date].push(value);
        });

        const rows = [];
        for (const date in dates) {
            if (dates.hasOwnProperty(date)) {
                rows.push(<Field name={date}
                                 component={Row}
                                 hotels={names}
                                 date={date}
                                 accommodations={dates[date]}/>);
            }
        }

        return <div>
            <label>Accommodation</label>
            <table className="table">
                <thead>
                <tr>
                    <th>Date</th>
                    {names.map((hotel) => {
                        return <th>{hotel}</th>;
                    })}
                </tr>
                </thead>
                <tbody>
                {rows}
                </tbody>
                <Price type={this.props.type} index={this.props.index}/>
            </table>
        </div>;
    }
}
