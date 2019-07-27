import * as React from 'react';
import {
    Event,
    Team,
} from '../../../fyziklani/helpers/interfaces';
import { lang } from '../../../i18n/i18n';
import Chart from './Chart';

export interface Data {
    events: {
        [eventId: number]: Event;
    };
    teams: {
        [eventId: number]: Team[];
    };
}

interface Props {
    data: Data;
}

export default class Timeline extends React.Component<Props, {}> {

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
