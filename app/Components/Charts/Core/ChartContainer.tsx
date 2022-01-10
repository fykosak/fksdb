import * as React from 'react';
import { ConnectedComponent } from 'react-redux';

interface OwnProps<ChartProps, LegendProps> {
    chart: React.ComponentType<ChartProps> | ConnectedComponent<React.ComponentType<any>, ChartProps>; // TODO
    chartProps: ChartProps;
    legendComponent?: React.ComponentType<LegendProps> | ConnectedComponent<React.ComponentType<any>, LegendProps>; // TODO
    legendProps?: LegendProps;
    headline?: React.ReactNode;
    containerClassName?: string;
}

export default class ChartContainer<ChartProps, LegendProps> extends React.Component<OwnProps<ChartProps, LegendProps>> {

    public render() {
        const {legendComponent, legendProps, headline, chartProps, chart, containerClassName} = this.props;
        return (
            <div className={'chart-container ' + (containerClassName ? containerClassName : '')}>
                {headline && <h3>{headline}</h3>}
                <div className={'row'}>
                    <div className="col-xl-9 col-lg-8 col-md-12">
                        {React.createElement<ChartProps>(chart, chartProps)}
                    </div>
                    {legendComponent && <div className="col-xl-3 col-lg-4 col-md-12">
                        {React.createElement<LegendProps>(legendComponent, legendProps)}
                    </div>}
                </div>
            </div>
        );
    }
}
