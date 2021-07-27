import ChartContainer from 'FKSDB/Components/Charts/Core/ChartContainer';
import GeoChart, { SCALE_LOG } from 'FKSDB/Components/Charts/Core/GeoCharts/GeoChart';
import { GeoData } from 'FKSDB/Components/Charts/Core/GeoCharts/geoChartHelper';
import * as React from 'react';

interface OwnProps {
    data: GeoData;
}

export default class ApplicationRationGeoChart extends React.Component<OwnProps, {}> {

    public render() {
        return <ChartContainer
            chart={GeoChart}
            chartProps={{data: this.props.data, scaleType: SCALE_LOG}}
            containerClassName="rating-geo-chart"
        />;
    }
}
