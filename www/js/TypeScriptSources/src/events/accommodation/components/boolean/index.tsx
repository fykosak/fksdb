import * as React from 'react';
import { IEventAccommodation } from '../../middleware/interfaces';
import Price from '../price';


interface IProps {
    accommodationDef?: IEventAccommodation[];
}

export default class Boolean extends React.Component<IProps, {}> {

    public render() {
        const {accommodationDef} = this.props;
        if (accommodationDef.length !== 1) {
            throw new Error('Wrong type of accommodation');
        }



        return <>
            <table className="table">
                <thead>
                <tr>
                    <th>Date</th>
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
