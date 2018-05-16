import * as React from 'react';

export default class ErrorDisplay extends React.Component<any, {}> {
    public render() {
        const {
            meta: {touched, error, warning},
        } = this.props;
        return <div>{touched && (
            (error && <span>{error}</span>) ||
            (warning && <span>{warning}</span>))
        }</div>;

    }
}
