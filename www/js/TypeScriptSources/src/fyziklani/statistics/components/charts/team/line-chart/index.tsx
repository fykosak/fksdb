import * as React from 'react';
import { lang } from '../../../../../../i18n/i18n';
import Card from '../../../../../../shared/components/card';
import Legend from '../legend';
import Chart from './chart';

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
