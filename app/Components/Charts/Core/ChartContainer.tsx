import * as React from 'react';
import { ConnectedComponent } from 'react-redux';

interface OwnProps<OwnChartProps, OwnLegendProps, ChartStateProps = unknown, LegendStateProps = unknown> {
    chart: React.ComponentType<OwnChartProps> | ConnectedComponent<React.ComponentType<ChartStateProps & OwnChartProps>, OwnChartProps>;
    chartProps: OwnChartProps;
    legendComponent?: React.ComponentType<OwnLegendProps> | ConnectedComponent<React.ComponentType<OwnLegendProps & LegendStateProps>, OwnLegendProps>;
    legendProps?: OwnLegendProps;
    headline?: React.ReactNode;
    containerClassName?: string;
}

export default class ChartContainer<OwnChartProps, OwnLegendProps, ChartStateProps = unknown, LegendStateProps = unknown> extends React.Component<OwnProps<OwnChartProps, OwnLegendProps, ChartStateProps, LegendStateProps>, never> {

    public render() {
        const {legendComponent, legendProps, headline, chartProps, chart, containerClassName} = this.props;
        return (
            <div className={'chart-container ' + (containerClassName ? containerClassName : '')}>
                {headline && <h3>{headline}</h3>}
                <div className="row">
                    <div className="col-xl-9 col-lg-8 col-md-12">
                        {React.createElement<OwnChartProps>(chart, chartProps)}
                    </div>
                    {legendComponent && <div className="col-xl-3 col-lg-4 col-md-12">
                        {React.createElement<OwnLegendProps>(legendComponent, legendProps)}
                    </div>}
                </div>
            </div>
        );
    }
}
