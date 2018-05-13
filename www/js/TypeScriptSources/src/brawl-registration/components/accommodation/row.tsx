import * as React from 'react';
import { IAccommodationItem } from './index';

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
                if (currentAcc[0].accId === value) {
                    cols.push(<td key={index} className="text-center table-success"
                                  onClick={() => {
                                      onChange(null);
                                  }}><span className="text-success fa fa-check"/></td>);
                } else {

                    cols.push(<td key={index} className="table-secondary" onClick={() => {
                        onChange(currentAcc[0].accId);
                    }}/>);

                }
            } else {
                cols.push(<td key={index} className="table-danger"/>);
            }
        });
        return <tr>
            <td><label>{date}</label></td>
            {cols}
        </tr>;
    }

}
