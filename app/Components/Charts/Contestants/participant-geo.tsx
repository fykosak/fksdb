import GeoChart, { SCALE_LOG } from 'FKSDB/Components/Charts/Core/GeoCharts/geo-chart';
import { GeoData } from 'FKSDB/Components/Charts/Core/GeoCharts/geo-helper';
import * as React from 'react';
import { availableLanguage, Translator } from '@translator/translator';

interface OwnProps {
    data: GeoData;
    translator: Translator<availableLanguage>;
}

export default class ParticipantGeo extends React.Component<OwnProps, never> {

    public render() {
        console.log(this.props.data);
        return <GeoChart data={this.props.data} scaleType={SCALE_LOG}/>
    }
}
