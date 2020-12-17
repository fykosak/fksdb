import { GeoData } from '@shared/components/geoChart/geoData';
import { geoNaturalEarth1, geoPath } from 'd3-geo';
import { ScaleLinear, ScaleLogarithmic } from 'd3-scale';
import * as React from 'react';

interface OwnProps {
    data: GeoData;
    activeColorScale: ScaleLinear<string, string> | ScaleLogarithmic<string, string>;
    inactiveColorScale: ScaleLinear<string, string> | ScaleLogarithmic<string, string>;
}

export default class GeoChart extends React.Component<OwnProps, { active?: string }> {

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
        const {data, activeColorScale, inactiveColorScale} = this.props;

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
        return <div>
            <svg viewBox={'-500 -300 1000 600'}>
                <g>
                    {countryNodes}
                </g>
            </svg>
        </div>;
    }
}
