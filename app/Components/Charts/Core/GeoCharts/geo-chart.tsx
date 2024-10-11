import { geoNaturalEarth1, geoPath } from 'd3-geo';
import { ScaleSequential, scaleSequential, scaleSequentialLog } from 'd3-scale';
import * as React from 'react';
import { useEffect, useState } from 'react';
import './geo-chart.scss';
import type { Feature } from 'geojson';
import { interpolateBuGn } from 'd3-scale-chromatic';

export interface GeoData {
    [countryISO: string]: number;
}

interface OwnProps {
    data: GeoData;
    scaleType: typeof SCALE_LINEAR | typeof SCALE_LOG;
}

export const SCALE_LINEAR = 'linear';
export const SCALE_LOG = 'log';

export default function GeoChart({data, scaleType}: OwnProps) {

    const [countryData, setData] = useState<Feature[]>([]);

    useEffect(() => {
        fetch('https://raw.githubusercontent.com/holtzy/D3-graph-gallery/master/DATA/world.geojson').then((response) => {
            return response.json();
        }).then((json) => {
            setData(json.features)
        });
    }, []);

    const max = Math.max(...Object.values(data));
    let colorScale: ScaleSequential<string> = null;
    switch (scaleType) {
        default:
        case SCALE_LINEAR:
            colorScale = scaleSequential(interpolateBuGn).domain([0, max + 1]);
            break;
        case SCALE_LOG:
            colorScale = scaleSequentialLog(interpolateBuGn).domain([0.1, max + 1]);
    }

    const projection = geoNaturalEarth1()
        .scale(180)
        .center([0, 0])
        .translate([0, 0]);

    const countryNodes = countryData.map((country, key) => {
        const count = Object.hasOwn(data, country.id) ? data[country.id] : 0;
        return <path
            key={key}
            style={{'--color': colorScale(count)} as React.CSSProperties}
            d={geoPath().projection(projection)(country)}
        >
            <title>{country.properties.name}: {count}</title>
        </path>;
    });
    return <div className="geo-chart">
        <svg viewBox="-500 -300 1000 600" className="chart">
            {countryNodes}
        </svg>
    </div>;
}
