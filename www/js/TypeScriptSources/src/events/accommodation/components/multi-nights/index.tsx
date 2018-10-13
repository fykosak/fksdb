import * as React from 'react';
import { IEventAccommodation } from '../../middleware/interfaces';
import Row from '../rows/single-row';
import { lang } from '../../../../i18n/i18n';

interface IProps {
    accommodationDef?: IEventAccommodation[];
}

export default class MultiNights extends React.Component<IProps, {}> {

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
