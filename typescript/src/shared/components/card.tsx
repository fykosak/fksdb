import * as React from 'react';

export interface OwnProps {
    children?: any;
    headline: string | JSX.Element;
    level: string;
}

export default class Card extends React.Component<OwnProps, {}> {

    public render() {
        const {level, headline, children} = this.props;
        return (
            <div className={'card border-' + level}>
                <div className={'card-header card-' + level}>{headline}</div>
                <div className="card-block card-body">
                    {children}
                </div>
            </div>
        );
    }
}
