import DateTimeFormatOptions = Intl.DateTimeFormatOptions;
import * as React from 'react';
import { Translator } from '@translator/translator';

interface OwnProps {
    date: string;
    options?: DateTimeFormatOptions;
    translator: Translator<string>;
}

export default function TimeDisplay(props: OwnProps) {
    const {options, translator} = props;
    const date = new Date(props.date);
    return <span>{date.toLocaleTimeString(translator.getBCP47(), options ? options : {
        hour: 'numeric',
        minute: 'numeric',
    })}</span>;
}
