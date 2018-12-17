import * as React from 'react';
import { lang } from '../../../../../../i18n/i18n';
import Chart from './chart';

export default class Timeline extends React.Component<{}, {}> {

    public render() {
        return (
            <div className={'fyziklani-chart-container'}>
                <h3>{lang.getText('timeProgress')}</h3>
                <Chart/>
            </div>
        );
    }
}
