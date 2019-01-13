import * as React from 'react';
import { lang } from '../../../i18n/i18n';
import DateTimeFormatOptions = Intl.DateTimeFormatOptions;

interface Props {
    date: string;
    options?: DateTimeFormatOptions;
}

export default class DateDisplay extends React.Component<Props, {}> {

    public render() {
        const {date, options} = this.props;
        const dateObject = new Date(date);
        return <span>{dateObject.toLocaleDateString(lang.getBCP47(), options)}</span>;
    }
}
