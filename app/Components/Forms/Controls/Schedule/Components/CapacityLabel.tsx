import * as React from 'react';
import { TranslatorContext } from '@translator/LangContext';

interface OwnProps {
    capacity: number;
    usedCapacity: number;
}

export default class CapacityLabel extends React.Component<OwnProps> {
    static contextType = TranslatorContext;
    public render() {
        const {capacity, usedCapacity} = this.props;
        if (capacity === null) {
            return null;
        }
        const translator = this.context;
        return <small
            className={'ms-3 ' + ((capacity <= usedCapacity) ? 'text-danger' : '')}>
            {translator.getText('Free capacity')}:&nbsp;{(capacity - usedCapacity)}
        </small>;
    }
}
