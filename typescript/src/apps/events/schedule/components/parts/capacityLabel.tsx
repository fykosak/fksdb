import { lang } from '@i18n/i18n';
import * as React from 'react';

interface OwnProps {
    capacity: number;
    usedCapacity: number;
}

export default class CapacityLabel extends React.Component<OwnProps, {}> {

    public render() {
        const {capacity, usedCapacity} = this.props;
        return <small
            className={'capacity-label ml-3 ' + ((capacity <= usedCapacity) ? 'text-danger' : '')}>
            {lang.getText('Used capacity/Total capacity')}: {usedCapacity}/{capacity}
        </small>;
    }
}
