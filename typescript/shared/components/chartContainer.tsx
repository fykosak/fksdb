import * as React from 'react';

interface OwnProps<T extends {}, L extends {} = {}> {
    chart: React.ComponentClass<T> | any; // TODO fix ConnectedComponent
    chartProps: T;
    legendComponent?: React.ComponentClass<T> | any;
    legendProps?: L;
    headline: string;
}

export default class ChartContainer<T, L> extends React.Component<OwnProps<T, L>, {}> {

    public render() {
        const {legendComponent, legendProps, headline, chartProps, chart} = this.props;
        return (
            <div className={'chart-container'}>
                <h3>{headline}</h3>
                <div className={'row'}>
                    <div className="col-12">
                        {React.createElement<T>(chart, chartProps)}
                    </div>
                    {legendComponent && <div className="col-12">
                        {React.createElement<L>(legendComponent, legendProps)}
                    </div>}
                </div>
            </div>
        );
    }
}
