import * as React from 'react';
import { useContext } from 'react';
import { TranslatorContext } from '@translator/context';

interface OwnProps {
    capacity: number;
    usedCapacity: number;
}

export default function CapacityLabel(props: OwnProps) {
    const translator = useContext(TranslatorContext);
    const {capacity, usedCapacity} = props;
    if (capacity === null) {
        return null;
    }
    return <small
        className={'ms-3 ' + ((capacity <= usedCapacity) ? 'text-danger' : '')}>
        {translator.getText('Free capacity')}:&nbsp;{(capacity - usedCapacity)}
    </small>;
}
