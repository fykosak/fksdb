import ChartContainer from 'FKSDB/Components/Charts/Core/ChartContainer';
import GeoChart, { SCALE_LOG } from 'FKSDB/Components/Charts/Core/GeoCharts/GeoChart';
import { GeoData } from 'FKSDB/Components/Charts/Core/GeoCharts/geoChartHelper';
import * as React from 'react';

interface OwnProps {
    data: GeoData;
}

export default class TeamsGeoChart extends React.Component<OwnProps, Record<string, never>> {

    public render() {
        return <ChartContainer
            chart={GeoChart}
            chartProps={{data: this.props.data, scaleType: SCALE_LOG}}
            containerClassName="teams-geo-chart"
        />;
    }
}
