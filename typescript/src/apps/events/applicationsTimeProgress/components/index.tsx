import { lang } from '@i18n/i18n';
import * as React from 'react';
import {
    Event,
    Team,
} from '../../../fyziklani/helpers/interfaces';
import Chart from './chart';

export interface Data {
    events: {
        [eventId: number]: Event;
    };
    teams: {
        [eventId: number]: Team[];
    };
}

interface OwnProps {
    data: Data;
}

export default class Timeline extends React.Component<OwnProps, {}> {

    public render() {
        const {data} = this.props;
        return (
            <div className={'fyziklani-chart-container'}>
                <h3>{lang.getText('Time progress')}</h3>
                <div className={'row'}>
                    <div className="col-12">
                        <Chart data={data}/>
                    </div>
                </div>
            </div>
        );
    }
}
