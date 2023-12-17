import * as React from 'react';
import { useContext } from 'react';
import { TranslatorContext } from '@translator/context';

interface OwnProps {
    capacity: number;
    usedCapacity: number;
}

export default function CapacityLabel({capacity, usedCapacity}: OwnProps) {
    const translator = useContext(TranslatorContext);
    if (capacity === null) {
        return null;
    }
    return <small
        className={'me-3 ' + ((capacity <= usedCapacity) ? 'text-danger' : '')}>
        {translator.getText('Free capacity')}:&nbsp;{(capacity - usedCapacity)}
    </small>;
}
