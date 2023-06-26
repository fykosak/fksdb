import GeoChart, { SCALE_LOG } from 'FKSDB/Components/Charts/Core/GeoCharts/GeoChart';
import { GeoData } from 'FKSDB/Components/Charts/Core/GeoCharts/geoChartHelper';
import * as React from 'react';
import { availableLanguage, Translator } from '@translator/translator';

interface OwnProps {
    data: GeoData;
    translator: Translator<availableLanguage>;
}

export default class ApplicationRationGeoChart extends React.Component<OwnProps, never> {

    public render() {
        return <GeoChart data={this.props.data} scaleType={SCALE_LOG}/>;
    }
}
