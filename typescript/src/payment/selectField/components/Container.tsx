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
        items.sort((a, b) => {
            if (b.personFamilyName > a.personFamilyName) {
                return -1;
            }
            if (b.personFamilyName < a.personFamilyName) {
                return 1;
            }
            return 0;
        });
        let lastPerson = null;
        items.forEach((value, index) => {
            if (lastPerson !== value.personId) {
                rows.push(<h3 key={value.personId}>{value.personName}</h3>);
                lastPerson = value.personId;
            }
            rows.push(<Row key={index} item={value}/>);
        });
        return <>{rows}</>;

    }
}
