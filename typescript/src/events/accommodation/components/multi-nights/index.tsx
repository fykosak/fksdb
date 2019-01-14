import * as React from 'react';
import { EventAccommodation } from '../../middleware/interfaces';
import Row from '../rows/single-row';

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
        accommodationDef.forEach((item, index) => {
            items.push(<Row key={index} accommodationItem={item}/>);
        });
        return <>
            {items}
        </>;
    }
}
