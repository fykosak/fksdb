import * as React from 'react';
import { PaymentAccommodationItem } from '../interfaces';
import Row from './Row';

interface Props {
    items: PaymentAccommodationItem[];
}

export default class Container extends React.Component<Props, {}> {

    public render() {
        const {items} = this.props;
        const rows = [];
        items.forEach((value, index) => {
            rows.push(<Row key={index} item={value}/>);
        });
        return <>{rows}</>;

    }
}
