import * as React from 'react';

interface IProps {
    date: string;
}

export default class DateDisplay extends React.Component<IProps, {}> {

    public render() {
        const date = new Date(this.props.date);
        return <span>{date.toLocaleDateString()}</span>;
    }
}
