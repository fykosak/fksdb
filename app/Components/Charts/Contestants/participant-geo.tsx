import GeoChart, { SCALE_LOG } from 'FKSDB/Components/Charts/Core/GeoCharts/geo-chart';
import { GeoData } from 'FKSDB/Components/Charts/Core/GeoCharts/geo-helper';
import * as React from 'react';
import { availableLanguage, Translator } from '@translator/translator';

interface OwnProps {
    data: GeoData;
    translator: Translator<availableLanguage>;
}

export default function ParticipantGeo(props: OwnProps) {
    return <GeoChart data={props.data} scaleType={SCALE_LOG}/>
}
