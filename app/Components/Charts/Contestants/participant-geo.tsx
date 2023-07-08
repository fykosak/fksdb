import GeoChart, { GeoData, SCALE_LOG } from 'FKSDB/Components/Charts/Core/GeoCharts/geo-chart';
import * as React from 'react';

interface OwnProps {
    data: GeoData;
}

export default function ParticipantGeo({data}: OwnProps) {
    return <GeoChart data={data} scaleType={SCALE_LOG}/>
}
