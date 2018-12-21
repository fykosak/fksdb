import * as React from 'react';
import { lang } from '../../../../../../i18n/i18n';
import Card from '../../../../../../shared/components/card';
import Legend from '../legend';
import Chart from './chart';

export default class TimeLine extends React.Component<{}, {}> {
    public render() {
        return (
            <Card headline={lang.getText('timeLine')} level={'info'}>
                <div className="row">
                    <Chart/>
                    <Legend inline={true}/>
                </div>
            </Card>
        );
    }
}
