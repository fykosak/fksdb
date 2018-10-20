import * as React from 'react';
import { lang } from '../../../../../../i18n/i18n';
import Legend from '../legend';
import Chart from './chart';
import Card from '../../../../../../shared/components/card';

export default class PointsInTime extends React.Component<{}, {}> {

    public render() {
        return (
            <Card headline={lang.getText('timeProgress')} level={'info'}>
                <div className="row">
                    <Chart/>
                    <Legend inline={false}/>
                </div>
            </Card>
        );
    }
}
