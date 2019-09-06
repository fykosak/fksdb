import * as React from 'react';
import { PaymentScheduleItem } from '../interfaces';
import Row from './row';

interface Props {
    items: PaymentScheduleItem[];
}

export default class Container extends React.Component<Props, {}> {

    public render() {
        const {items} = this.props;
        const rows = [];
        items.sort((a, b) => {
            return a.personFamilyName.localeCompare(b.personFamilyName);
        });
        let lastPerson = null;
        items.forEach((value, index) => {
            if (lastPerson !== value.personId) {
                rows.push(<h3 key={'h' + index}>{value.personName}</h3>);
                lastPerson = value.personId;
            }
            rows.push(<Row key={index} item={value}/>);
        });
        return <>{rows}</>;
    }
}
