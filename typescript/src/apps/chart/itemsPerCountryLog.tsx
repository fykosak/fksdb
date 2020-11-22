import GeoChart from "@shared/components/geoChart/geoChart";
import { findMax, GeoData } from "@shared/components/geoChart/geoData";
import { scaleLog } from 'd3-scale';
import * as React from 'react';

interface OwnProps {
    data: GeoData;
}

export default class ItemsPerCountryLog extends React.Component<OwnProps, {}> {

    public render() {
        const {data} = this.props;
        const max = findMax(data);
        const inActiveColorScale = scaleLog<string, string>();
        const activeColorScale = scaleLog<string, string>();

        inActiveColorScale.domain([0.1, max]);
        activeColorScale.domain([0.1, max]);
        inActiveColorScale.range(['#fff', '#007bff']);
        activeColorScale.range(['#fff', '#dc3545']);

        return <GeoChart data={data} activeColorScale={activeColorScale} inactiveColorScale={inActiveColorScale}/>;
    }
}
