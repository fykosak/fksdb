import * as React from 'react';
import { IEventAccommodation } from '../../middleware/interfaces';
import Price from '../price';
import Row from './row';
import { lang } from '../../../../i18n/i18n';

interface IProps {
    accommodationDef?: IEventAccommodation[];
}

export default class Matrix extends React.Component<IProps, {}> {

    public render() {
        const dates = {};
        const names = [];
        const {accommodationDef} = this.props;
        if (!accommodationDef) {
            return null;
        }
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
                rows.push(<Row
                    key={date}
                    hotels={names}
                    date={date}
                    accommodations={dates[date]}
                />);
            }
        }

        return <>
            <table className="table">
                <thead>
                <tr>
                    <th>{lang.getText('Date')}</th>
                    {names.map((hotel, i) => {
                        return <th key={i}>{hotel}</th>;
                    })}
                </tr>
                </thead>
                <tbody>
                {rows}
                </tbody>
            </table>
            <Price accommodationDef={accommodationDef}/>
        </>;
    }
}
