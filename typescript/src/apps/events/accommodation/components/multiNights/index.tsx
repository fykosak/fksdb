import * as React from 'react';
import { EventAccommodation } from '../../middleware/interfaces';
import Row from '../rows/singleRow';

interface Props {
    accommodationDef: EventAccommodation[];
}

export default class MultiNights extends React.Component<Props, {}> {

    public render() {
        const {accommodationDef} = this.props;
        if (accommodationDef.length === 1) {
            console.warn('You can use single type');
        }
        const items = [];
        accommodationDef.sort((a, b) => {
            if (a.date > b.date) {
                return -1;
            }
            if (a.date < b.date) {
                return 1;
            }
            return 0;
        });
        accommodationDef.forEach((item, index) => {
            items.push(<Row key={index} accommodationItem={item}/>);
        });
        return <>
            {items}
        </>;
    }
}
