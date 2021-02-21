import ChartContainer from 'FKSDB/Components/Charts/Core/ChartContainer';
import GeoChartComponent, { SCALE_LOG } from 'FKSDB/Components/Charts/Core/GeoCharts/GeoChartComponent';
import { GeoData } from 'FKSDB/Components/Charts/Core/GeoCharts/geoChartHelper';
import * as React from 'react';

interface OwnProps {
    data: GeoData;
}

export default class ApplicationRationGeoChart extends React.Component<OwnProps, {}> {

    public render() {
        return <ChartContainer
            chart={GeoChartComponent}
            chartProps={{data: this.props.data, scaleType: SCALE_LOG}}
            containerClassName="rating-geo-chart"
        />;
    }
}
