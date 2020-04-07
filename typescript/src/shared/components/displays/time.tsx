import { lang } from '@i18n/i18n';
import * as React from 'react';
import DateTimeFormatOptions = Intl.DateTimeFormatOptions;

interface OwnProps {
    date: string;
    options?: DateTimeFormatOptions;
}

export default class TimeDisplay extends React.Component<OwnProps, {}> {

    public render() {
        const {options} = this.props;
        const date = new Date(this.props.date);
        return <span>{date.toLocaleTimeString(lang.getBCP47(), options ? options : {
            hour: 'numeric',
            minute: 'numeric',
        })}</span>;
    }
}
