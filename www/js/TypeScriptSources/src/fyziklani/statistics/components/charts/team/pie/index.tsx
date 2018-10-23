import * as React from 'react';
import { lang } from '../../../../../../i18n/i18n';
import Card from '../../../../../../shared/components/card';
import Legend from '../legend';
import Chart from './chart';

export default class PointsPie extends React.Component<{}, {}> {

    public render() {
        return (
            <Card headline={lang.getText('successOfSubmitting')} level={'info'}>
                <Chart/>
                <Legend inline={false}/>
            </Card>);
    }
}
