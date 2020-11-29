import GeoChart from '@shared/components/geoChart/geoChart';
import { findMax, GeoData } from '@shared/components/geoChart/geoData';
import { scaleLinear } from 'd3-scale';
import * as React from 'react';

interface OwnProps {
    data: GeoData;
}

export default class ItemsPerCountryLinear extends React.Component<OwnProps, {}> {

    public render() {
        const {data} = this.props;
        const max = findMax(data);
        const inActiveColorScale = scaleLinear<string, string>();
        const activeColorScale = scaleLinear<string, string>();

        inActiveColorScale.domain([0, max + 1]);
        activeColorScale.domain([0, max + 1]);
        inActiveColorScale.range(['#dc3545', '#28a745']);
        activeColorScale.range(['#fc5565', '#48c765']);

        return <GeoChart data={data} activeColorScale={activeColorScale} inactiveColorScale={inActiveColorScale}/>;
    }
}
