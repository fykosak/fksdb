import DateTimeFormatOptions = Intl.DateTimeFormatOptions;
import * as React from 'react';
import { Translator } from '@translator/translator';

interface OwnProps {
    date: string;
    options?: DateTimeFormatOptions;
    translator: Translator<string>;
}

export default class TimeDisplay extends React.Component<OwnProps, never> {

    public render() {
        const {options, translator} = this.props;
        const date = new Date(this.props.date);
        return <span>{date.toLocaleTimeString(translator.getBCP47(), options ? options : {
            hour: 'numeric',
            minute: 'numeric',
        })}</span>;
    }
}
