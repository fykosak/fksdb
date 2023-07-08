import * as React from 'react';
import { useEffect, useState } from 'react';
import { useSelector } from 'react-redux';
import './timer.scss';
import { Store } from '../../../reducers/store';

interface OwnProps {
    mode: 'big' | 'small';
}

export const getCurrentDelta = (now: Date, toStart: number, toEnd: number, inserted: Date | null): {
    toStart: number;
    toEnd: number;
} => {
    if (!inserted) {
        return {
            toEnd: 0,
            toStart: 0,
        };
    }
    const delta = now.getTime() - inserted.getTime();
    return {
        toEnd: toEnd - delta,
        toStart: toStart - delta,
    };
};


export default function Timer({mode}: OwnProps) {
    const inserted = useSelector((state: Store) => state.timer.inserted);
    const rawToEnd = useSelector((state: Store) => state.timer.toEnd);
    const rawToStart = useSelector((state: Store) => state.timer.toStart);
    const [now, setDate] = useState(new Date());
    useEffect(() => {
        const timerId = setInterval(() => setDate(new Date()), 1000);
        return () => clearInterval(timerId);
    }, []);

    const {toStart, toEnd} = getCurrentDelta(now, rawToStart, rawToEnd, inserted);

    let timeStamp = 0;
    if (toStart > 0) {
        timeStamp = toStart;
    } else if (toEnd > 0) {
        timeStamp = toEnd;
    } else {
        return null;
    }
    const date = new Date(timeStamp);
    const h = date.getUTCHours();
    const m = date.getUTCMinutes();
    const s = date.getUTCSeconds();
    return <div className={'row presentation-timer timer-' + mode}>
        <span className="col">
                    <span className="time-value">{(h < 10 ? '0' + h : '' + h)}</span>
                    <span className="time-label">Hours/Hodin</span>
                </span>
        <span className="col">
                    <span className="time-value">{(m < 10 ? '0' + m : '' + m)}</span>
                    <span className="time-label">Minutes/Minut</span>
                </span>
        <span className="col">
                    <span className="time-value">{(s < 10 ? '0' + s : '' + s)}</span>
                    <span className="time-label">Seconds/Sekund</span>
        </span>
    </div>;
}
