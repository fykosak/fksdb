import * as React from 'react';
import { lang } from '../../../i18n/i18n';

interface IProps {
    capacity: number;
    usedCapacity: number;
}

export default class CapacityLabel extends React.Component<IProps, {}> {

    public render() {
        const {capacity, usedCapacity} = this.props;
        return <small
            className={'ml-3 ' + ((capacity <= usedCapacity) ? 'text-danger' : '')}>
            {lang.getText('Used capacity/Total capacity:')} {usedCapacity}/{capacity}
        </small>;
    }
}
