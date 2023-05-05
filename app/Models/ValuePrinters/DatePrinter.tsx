import * as React from 'react';
import { availableLanguage, Translator } from '@translator/translator';
import DateTimeFormatOptions = Intl.DateTimeFormatOptions;

interface OwnProps {
    date: string;
    options?: DateTimeFormatOptions;
    translator: Translator<availableLanguage>;
}

export default class DateDisplay extends React.Component<OwnProps> {

    public render() {
        const {date, options, translator} = this.props;
        const dateObject = new Date(date);
        return <span>{dateObject.toLocaleDateString(translator.getBCP47(), options)} {dateObject.toLocaleTimeString(translator.getBCP47(), options)}</span>;
    }
}
