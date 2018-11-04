import * as React from 'react';
import TimeDisplay from '../../../shared/components/displays/time';

interface IProps {
    start: string;
    end: string;
}

export default class TimeLabel extends React.Component<IProps, {}> {

    public render() {
        const {start, end} = this.props;
        return (<div className={'schedule-time h-100 d-flex align-items-center text-center'}>
                <div className={'w-100'}>
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
