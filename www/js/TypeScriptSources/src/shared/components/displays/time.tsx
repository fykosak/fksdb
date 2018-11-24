import * as React from 'react';
import { lang } from '../../../i18n/i18n';
import DateTimeFormatOptions = Intl.DateTimeFormatOptions;

interface IProps {
    date: string;
    options?: DateTimeFormatOptions;
}

export default class TimeDisplay extends React.Component<IProps, {}> {

    public render() {
        const {options} = this.props;
        const date = new Date(this.props.date);
        return <span>{date.toLocaleTimeString(lang.getBCP47(), options)}</span>;
    }
}
