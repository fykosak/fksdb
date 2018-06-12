import * as React from 'react';

export interface IProps {
    children?: any;
    headline: string | JSX.Element;
    level: string;
}

export default class Card extends React.Component<IProps, {}> {

    public render() {
        return (
            <div className={'card card-outline-' + this.props.level}>
                <div className={'card-header card-' + this.props.level}>{this.props.headline}</div>
                <div className="card-block card-body">
                    {this.props.children}
                </div>
            </div>
        );
    }
}
