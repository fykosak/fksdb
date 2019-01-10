import * as React from 'react';
import {
    IEvent,
    ITeam,
} from '../../../fyziklani/helpers/interfaces';
import { lang } from '../../../i18n/i18n';
import Chart from './chart';

export interface IData {
    events: {
        [eventId: number]: IEvent;
    };
    teams: {
        [eventId: number]: ITeam[];
    };
}

interface IProps {
    data: IData;
}

export default class Timeline extends React.Component<IProps, {}> {

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
