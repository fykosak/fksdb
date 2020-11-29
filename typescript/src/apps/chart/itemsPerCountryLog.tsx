import GeoChart from '@shared/components/geoChart/geoChart';
import { findMax, GeoData } from '@shared/components/geoChart/geoData';
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

        inActiveColorScale.domain([0.1, max + 1]);
        activeColorScale.domain([0.1, max + 1]);
        inActiveColorScale.range(['#dc3545', '#28a745']);
        activeColorScale.range(['#fc5565', '#48c765']);

        return <GeoChart data={data} activeColorScale={activeColorScale} inactiveColorScale={inActiveColorScale}/>;
    }
}
