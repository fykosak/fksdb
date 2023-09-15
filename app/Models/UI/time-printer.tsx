import DateTimeFormatOptions = Intl.DateTimeFormatOptions;
import * as React from 'react';
import { Translator } from '@translator/translator';

interface OwnProps {
    date: string;
    options?: DateTimeFormatOptions;
    translator: Translator;
}

export default function TimePrinter({options, translator, date}: OwnProps) {
    return <span>{(new Date(date)).toLocaleTimeString(translator.getBCP47(), options ? options : {
        hour: 'numeric',
        minute: 'numeric',
    })}</span>;
}
