import * as React from 'react';
import { useEffect, useState } from 'react';
import { useSelector } from 'react-redux';
import { getCurrentDelta } from './timer';
import './images.scss';
import { Store } from '../../../reducers/store';

export default function Images() {
    const inserted = useSelector((state: Store) => state.timer.inserted);
    const rawToEnd = useSelector((state: Store) => state.timer.toEnd);
    const rawToStart = useSelector((state: Store) => state.timer.toStart);
    const [now, setDate] = useState(new Date());
    useEffect(() => {
        const timerId = setInterval(() => setDate(new Date()), 1000);
        return () => clearInterval(timerId);
    }, []);
    const {toStart, toEnd} = getCurrentDelta(now, rawToStart, rawToEnd, inserted);
    const getLabel = (toStart: number, toEnd: number): string => {
        if (toStart > 300 * 1000) {
            return 'Have not begun yet / Ješte nezačalo';
        }
        if (toStart > 0) {
            return 'Will soon begin / Brzy začne';
        }
        if (toStart > -120 * 1000) {
            return 'Start!';
        }
        if (toEnd > 0) {
            return null;
        }
        if (toEnd > -240 * 1000) {
            return 'The End / Konec';
        }
        return 'Waiting for results / Čekání na výsledky';
    }

    if (toStart === 0 || toEnd === 0) {
        return null;
    }

    const label = getLabel(toStart, toEnd);
    if (label === null) {
        return null;
    }

    return <div className="presentation-images">
        {label}
    </div>;
}
