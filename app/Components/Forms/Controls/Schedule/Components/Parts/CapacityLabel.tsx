import { translator } from '@translator/translator';
import * as React from 'react';

interface OwnProps {
    capacity: number;
    usedCapacity: number;
}

export default class CapacityLabel extends React.Component<OwnProps> {

    public render() {
        const {capacity, usedCapacity} = this.props;
        if (capacity === null) {
            return null;
            /* <small
                className={'capacity-label ms-3'}>
                {lang.getText('Used capacity')}: {usedCapacity}
            </small>;*/
        }
        return <small
            className={'capacity-label ms-3 ' + ((capacity <= usedCapacity) ? 'text-danger' : '')}>
            {translator.getText('Free capacity')}: {(capacity - usedCapacity)}
        </small>;
    }
}
