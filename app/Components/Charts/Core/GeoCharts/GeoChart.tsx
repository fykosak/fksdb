import { geoNaturalEarth1, geoPath } from 'd3-geo';
import { scaleLinear, scaleLog } from 'd3-scale';
import * as React from 'react';
import { findMax, GeoData } from './geoChartHelper';

interface OwnProps {
    data: GeoData;
    scaleType: typeof SCALE_LINEAR | typeof SCALE_LOG;
}

export const SCALE_LINEAR = 'linear';
export const SCALE_LOG = 'log';
export default abstract class GeoChart extends React.Component<OwnProps, { active?: string }> {

    private countryData = [];

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
        let inactiveColorScale = null;
        let activeColorScale = null;
        switch (scaleType) {
            default:
            case SCALE_LINEAR:
                inactiveColorScale = scaleLinear<string, string>();
                activeColorScale = scaleLinear<string, string>();
                inactiveColorScale.domain([0, max + 1]);
                activeColorScale.domain([0, max + 1]);
                break;
            case SCALE_LOG:
                inactiveColorScale = scaleLog<string, string>();
                activeColorScale = scaleLog<string, string>();
                inactiveColorScale.domain([0.1, max + 1]);
                activeColorScale.domain([0.1, max + 1]);
        }
        inactiveColorScale.range(['#dc3545', '#28a745']);
        activeColorScale.range(['#fc5565', '#48c765']);

        const projection = geoNaturalEarth1()
            .scale(180)
            .center([0, 0])
            .translate([0, 0]);

        const countryNodes = [];
        this.countryData.forEach((country, key) => {
            const isActive = this.state && country.id === this.state.active;
            const count = data.hasOwnProperty(country.id) ? data[country.id].count : 0;
            countryNodes.push(<path
                key={key}
                fill={isActive ? activeColorScale(count) : inactiveColorScale(count)}
                stroke={'#000'}
                strokeWidth={0.5}
                onMouseOver={() => {
                    this.setState({active: country.id});
                }}
                onMouseLeave={() => {
                    this.setState({active: null});
                }}
                d={geoPath().projection(projection)(country)}
            ><title>{country.properties.name}: {count}</title>
            </path>);
        });
        return <svg viewBox={'-500 -300 1000 600'} className="chart geo-chart">
            <g>
                {countryNodes}
            </g>
        </svg>;
    }
}
