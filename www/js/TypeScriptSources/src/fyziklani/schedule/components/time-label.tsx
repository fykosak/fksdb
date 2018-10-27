import * as React from 'react';
import DateDisplay from '../../../shared/components/displays/date';
import TimeDisplay from '../../../shared/components/displays/time';

interface IProps {
    start: string;
    end: string;
}

export default class TimeLabel extends React.Component<IProps, {}> {

    public render() {
        const {start, end} = this.props;
        return (<div className={'schedule-time'}>
                <div>
                    <DateDisplay date={start} options={{weekday: 'long'}}/>
                </div>
                <div>
                    <TimeDisplay
                        date={start}
                        options={{hour: 'numeric', minute: 'numeric'}}
                    /> - <TimeDisplay
                    date={end}
                    options={{hour: 'numeric', minute: 'numeric'}}
                />
                </div>
            </div>
        );
    }
}
