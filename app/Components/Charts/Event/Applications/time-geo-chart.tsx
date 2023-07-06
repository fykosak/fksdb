import GeoChart, { SCALE_LOG } from 'FKSDB/Components/Charts/Core/GeoCharts/geo-chart';
import { GeoData } from 'FKSDB/Components/Charts/Core/GeoCharts/geo-helper';
import * as React from 'react';
import { useState } from 'react';
import { availableLanguage, Translator } from '@translator/translator';
import Range from 'FKSDB/Components/Charts/Event/Applications/range';

interface OwnProps {
    data: Array<{
        country: string;
        created: string;
        createdBefore: number;
    }>;
    translator: Translator<availableLanguage>;
}

export default function TimeGeoChart({data, translator}: OwnProps) {
    const geoData: GeoData = {};
    const [time, setTime] = useState<number>(0);
    data.forEach((datum) => {
        const delta = datum.createdBefore / 3600;
        if (delta < time) {
            geoData[datum.country] = geoData[datum.country] || 0;
            geoData[datum.country]++;
        }
    });
    const min = Math.min(...data.map(datum =>
        (datum.createdBefore) / 3600),
    );
    return <>
        <Range min={min} onChange={(value: number) => setTime(value)} value={time} translator={translator}/>
        <GeoChart data={geoData} scaleType={SCALE_LOG}/>
    </>;
}
