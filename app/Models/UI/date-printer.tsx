import * as React from 'react';
import { Translator } from '@translator/translator';
import DateTimeFormatOptions = Intl.DateTimeFormatOptions;

interface OwnProps {
    date: string;
    options?: DateTimeFormatOptions;
    translator: Translator;
}

export default function DateDisplay({date, options, translator}: OwnProps) {
    const dateObject = new Date(date);
    return <span>{dateObject.toLocaleDateString(translator.getBCP47(), options)} {dateObject.toLocaleTimeString(translator.getBCP47(), options)}</span>;
}
