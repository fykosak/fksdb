import * as React from 'react';

interface OwnProps<T extends {}, L extends {} = Record<string, never>> {
    chart: React.ComponentClass<T> | any; // TODO fix ConnectedComponent
    chartProps: T;
    legendComponent?: React.ComponentClass<T> | any;
    legendProps?: L;
    headline?: string;
    containerClassName?: string;
}

export default class ChartContainer<T, L> extends React.Component<OwnProps<T, L>, Record<string, never>> {

    public render() {
        const {legendComponent, legendProps, headline, chartProps, chart, containerClassName} = this.props;
        return (
            <div className={'chart-container ' + (containerClassName ? containerClassName : '')}>
                {headline && <h3>{headline}</h3>}
                <div className={'row'}>
                    <div className="col-xl-9 col-lg-8 col-md-12">
                        {React.createElement<T>(chart, chartProps)}
                    </div>
                    {legendComponent && <div className="col-xl-3 col-lg-4 col-md-12">
                        {React.createElement<L>(legendComponent, legendProps)}
                    </div>}
                </div>
            </div>
        );
    }
}
