import { translator } from '@translator/translator';
import * as React from 'react';
import DateTimeFormatOptions = Intl.DateTimeFormatOptions;

interface OwnProps {
    date: string;
    options?: DateTimeFormatOptions;
}

export default class DateDisplay extends React.Component<OwnProps> {

    public render() {
        const {date, options} = this.props;
        const dateObject = new Date(date);
        return <span>{dateObject.toLocaleDateString(translator.getBCP47(), options)}</span>;
    }
}
