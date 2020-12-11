import { lang } from '@i18n/i18n';
import * as React from 'react';

interface OwnProps {
    capacity: number;
    usedCapacity: number;
}

export default class CapacityLabel extends React.Component<OwnProps, {}> {

    public render() {
        const {capacity, usedCapacity} = this.props;
        if (capacity === null) {
            return null;
            /* <small
                className={'capacity-label ml-3'}>
                {lang.getText('Used capacity')}: {usedCapacity}
            </small>;*/
        }
        return <small
            className={'capacity-label ml-3 ' + ((capacity <= usedCapacity) ? 'text-danger' : '')}>
            {lang.getText('Free capacity')}: {(capacity - usedCapacity)}
        </small>;
    }
}
