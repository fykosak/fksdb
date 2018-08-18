import * as React from 'react';

export interface IProps {
    children?: any;
    headline: string;
    level: string;
}

export default class Card extends React.Component<IProps, {}> {

    public render() {
        const {level, headline, children} = this.props;
        return (
            <div className={'card card-outline-' + level}>
                <div className={'card-header card-' + level}>{headline}</div>
                <div className="card-block card-body">
                    {children}
                </div>
            </div>
        );
    }
}
