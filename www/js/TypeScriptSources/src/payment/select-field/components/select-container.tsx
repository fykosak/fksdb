import * as React from 'react';
import { IPaymentAccommodationItem } from '../interfaces';
import Row from './row';

interface IProps {
    items: IPaymentAccommodationItem[];
}

export default class Index extends React.Component<IProps, {}> {

    public render() {
        const {items} = this.props;
        const rows = [];
        items.forEach((value, index) => {
            rows.push(<Row key={index} item={value}/>);
        });
        return <>{rows}</>;

    }
}
