import { geoNaturalEarth1, geoPath } from 'd3-geo';
import { scaleSequential, scaleSequentialLog } from 'd3-scale';
import * as React from 'react';
import { findMax, GeoData } from './geo-helper';
import './geo-chart.scss';
import type { Feature } from 'geojson';
import { interpolateBuGn } from 'd3-scale-chromatic';

interface OwnProps {
    data: GeoData;
    scaleType: typeof SCALE_LINEAR | typeof SCALE_LOG;
}

export const SCALE_LINEAR = 'linear';
export const SCALE_LOG = 'log';

export default class GeoChart extends React.Component<OwnProps, { active?: string }> {

    private countryData: Feature[] = [];

    public componentDidMount() {
        fetch('https://raw.githubusercontent.com/holtzy/D3-graph-gallery/master/DATA/world.geojson').then((response) => {
            return response.json();
        }).then((json) => {
            this.countryData = json.features;
            this.forceUpdate();
        });
    }

    public render() {
        const {data, scaleType} = this.props;
        const max = findMax(data);
        let colorScale = null;
        switch (scaleType) {
            default:
            case SCALE_LINEAR:
                colorScale = scaleSequential(interpolateBuGn);
                // colorScale = scaleLinear<string, string>();
                colorScale.domain([0, max + 1]);
                break;
            case SCALE_LOG:
                colorScale = scaleSequentialLog(interpolateBuGn);
                //colorScale = scaleLog<string, string>();
                colorScale.domain([0.1, max + 1]);
        }

        const projection = geoNaturalEarth1()
            .scale(180)
            .center([0, 0])
            .translate([0, 0]);

        const countryNodes = [];
        this.countryData.forEach((country, key) => {
            const count = Object.hasOwn(data, country.id) ? data[country.id] : 0;
            countryNodes.push(<path
                key={key}
                style={{'--color': colorScale(count)} as React.CSSProperties}
                d={geoPath().projection(projection)(country)}
            >
                <title>{country.properties.name}: {count}</title>
            </path>);
        });
        return <div className="geo-chart">
            <svg viewBox="-500 -300 1000 600" className="chart">
                {countryNodes}
            </svg>
        </div>;
    }
}
