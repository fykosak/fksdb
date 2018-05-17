import * as React from 'react';

interface IProps {
    eur: number;
    kc: number;
}

export default class PriceDisplay extends React.Component<IProps, {}> {

    public render() {
        return <span>{this.props.eur} €/{this.props.kc} Kč</span>;
    }
}
