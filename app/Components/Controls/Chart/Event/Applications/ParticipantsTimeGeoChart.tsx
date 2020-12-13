import * as React from 'react';
import { GeoData } from '@FKSDB/Components/Controls/Chart/Core/GeoCharts/geoChartHelper';
import ChartContainer from '@FKSDB/Components/Controls/Chart/Core/ChartContainer';
import GeoChartComponent, { SCALE_LOG } from '@FKSDB/Components/Controls/Chart/Core/GeoCharts/GeoChartComponent';

interface OwnProps {
    data: Array<{
        country: string;
        created: string;
    }>;
}

export default class ParticipantsTimeGeoChart extends React.Component<OwnProps, { timestamp: number }> {

    public render() {
        const day = (1000 * 60 * 60 * 24);
        const {data} = this.props;
        let maxTimestamp = 0;
        let minTimestamp = (new Date()).getTime();

        const geoData: GeoData = {};

        data.forEach((datum) => {
            const time = (new Date(datum.created)).getTime();
            maxTimestamp = maxTimestamp > time ? maxTimestamp : time;
            minTimestamp = minTimestamp < time ? minTimestamp : time;
            if (!this.state || time < this.state.timestamp) {
                geoData[datum.country] = geoData[datum.country] || {count: 0};
                geoData[datum.country].count++;
            }
        });
        const value = this.state ? this.state.timestamp : maxTimestamp;
        return <div className="chart-container participant-time-geo-chart">
            <div className="form-group">
                <input type="range"
                       step={day}
                       className="form-control"
                       max={Math.ceil(maxTimestamp / day) * day}
                       min={Math.floor(minTimestamp / day) * day}
                       onChange={(event) => {
                           this.setState({timestamp: +event.target.value});
                       }}
                       value={value}/>
                <small className="form-text text-muted">{(new Date(value)).toISOString()}</small>
            </div>
            <ChartContainer chart={GeoChartComponent} chartProps={{data: geoData, scaleType: SCALE_LOG}}/>
        </div>;
    }
}
