import { lang } from '@i18n/i18n';
import * as React from 'react';
import DateTimeFormatOptions = Intl.DateTimeFormatOptions;

// TODO
interface OwnProps {
    date: string;
    options?: DateTimeFormatOptions;
}

export default class DateDisplay extends React.Component<OwnProps, {}> {

    public render() {
        const {date, options} = this.props;
        const dateObject = new Date(date);
        return <span>{dateObject.toLocaleDateString(lang.getBCP47(), options)}</span>;
    }
}
