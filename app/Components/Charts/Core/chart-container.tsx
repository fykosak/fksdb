import * as React from 'react';
import { ConnectedComponent } from 'react-redux';

interface OwnProps<OwnChartProps, OwnLegendProps, ChartStateProps = unknown, LegendStateProps = unknown> {
    chart: React.ComponentType<OwnChartProps> | ConnectedComponent<React.ComponentType<ChartStateProps & OwnChartProps>, OwnChartProps>;
    chartProps: OwnChartProps;
    legendComponent: React.ComponentType<OwnLegendProps> | ConnectedComponent<React.ComponentType<OwnLegendProps & LegendStateProps>, OwnLegendProps>;
    legendProps?: OwnLegendProps;
}

export default class ChartContainer<OwnChartProps, OwnLegendProps, ChartStateProps = unknown, LegendStateProps = unknown> extends React.Component<OwnProps<OwnChartProps, OwnLegendProps, ChartStateProps, LegendStateProps>, never> {

    public render() {
        const {legendComponent, legendProps, chartProps, chart} = this.props;
        return <div>
            <div>
                {React.createElement<OwnChartProps>(chart, chartProps)}
            </div>
            <h3>Legend</h3>
            <div>
                {React.createElement<OwnLegendProps>(legendComponent, legendProps)}
            </div>
        </div>;
    }
}
