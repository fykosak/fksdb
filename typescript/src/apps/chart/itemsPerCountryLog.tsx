import GeoChart from "@shared/components/geoChart/geoChart";
import { scaleLog } from 'd3-scale';
import * as React from 'react';

interface OwnProps {
    data: Array<{
        country: string;
        count: number;
    }>;
}

export default class ItemsPerCountryLog extends React.Component<OwnProps, {}> {

    public render() {
        const {data} = this.props;

        let max = 0;
        data.forEach((datum) => {
            max = max > datum.count ? max : datum.count;
        });
        const inActiveColorScale = scaleLog<string, string>();
        const activeColorScale = scaleLog<string, string>();

        inActiveColorScale.domain([0.1, max]);
        activeColorScale.domain([0.1, max]);
        inActiveColorScale.range(['#fff', '#007bff']);
        activeColorScale.range(['#fff', '#dc3545']);

        return <GeoChart data={data} activeColorScale={activeColorScale} inactiveColorScale={inActiveColorScale}/>;
    }
}
