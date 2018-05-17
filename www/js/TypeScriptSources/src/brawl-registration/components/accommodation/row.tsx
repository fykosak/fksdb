import * as React from 'react';
import { IAccommodationItem } from '../../middleware/iterfaces';
import DateDisplay from '../displays/date';

interface IProps {
    names: string[];
    accommodations: IAccommodationItem[];
}

export default class Row extends React.Component<IProps & any, {}> {
    public render() {
        const {hotels, accommodations, input: {onChange, value}, date} = this.props;
        if (!accommodations) {
            return null;
        }
        const cols = [];
        hotels.forEach((name, index) => {
            const currentAcc = accommodations.filter((acc) => {
                return acc.name === name;
            });
            if (currentAcc.length) {
                const priceLabel = <small
                    className="align-bottom text-muted">{currentAcc[0].price.kc + 'Kč /' + currentAcc[0].price.eur + '€'}</small>;
                if (currentAcc[0].accId === value) {
                    cols.push(<td key={index} className="text-center table-success"
                                  onClick={() => {
                                      onChange(null);
                                  }}>
                        <div>
                            <span className="text-success fa fa-check"/>
                        </div>
                        <div>
                            {priceLabel}
                        </div>
                    </td>);
                } else {

                    cols.push(<td key={index} className="text-center table-secondary" onClick={() => {
                        onChange(currentAcc[0].accId);
                    }}>
                        <div>{priceLabel}</div>
                    </td>);

                }
            } else {
                cols.push(<td key={index} className="table-danger"/>);
            }
        });
        return <tr>
            <td><label><DateDisplay date={date}/></label></td>
            {cols}
        </tr>;
    }
}
