import * as React from 'react';

export interface OwnProps {
    children?: React.ReactNode;
    headline: string | JSX.Element;
    level: string;
}

export default function Card(props: OwnProps) {
    const {level, headline, children} = props;
    return <div className={'card border-' + level}>
        <div className={'card-header card-' + level}>{headline}</div>
        <div className="card-block card-body">
            {children}
        </div>
    </div>;
}
