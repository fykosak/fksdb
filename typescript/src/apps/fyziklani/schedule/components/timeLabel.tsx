import TimeDisplay from '@shared/components/displays/time';
import * as React from 'react';

interface OwnProps {
    start: string;
    end: string;
}

export default class TimeLabel extends React.Component<OwnProps, {}> {

    public render() {
        const {start, end} = this.props;
        return (
            <div className={'schedule-time h-100 d-flex align-items-center text-center'}>
                <div className={'w-100'}>
                    <TimeDisplay
                        date={start}
                    /> - <TimeDisplay
                    date={end}
                />
                </div>
            </div>
        );
    }
}
