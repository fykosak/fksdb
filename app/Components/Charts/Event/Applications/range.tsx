import * as React from 'react';
import { useState } from 'react';
import { availableLanguage, Translator } from '@translator/translator';

interface OwnProps {
    min: number;
    onChange: (value: number) => void;
    value: number;
    translator: Translator<availableLanguage>;
}

export default function Range({min, onChange, value}: OwnProps) {
    const [play, setPlay] = useState<boolean>(false);
    if (play) {
        window.setTimeout(() => {
            if (value > 0) {
                setPlay(false);
                onChange(0);
            } else if (play) {
                onChange(value + 1);
            }
        }, 50);
    }
    return <div>
        <input
            type="range"
            className="w-100"
            max={0}
            min={min}
            step={1}
            onChange={(event) => {
                onChange(+event.target.value);
            }}
            value={value}
        />
        <span>To event: {Math.floor(-value / 24)} days + {Math.floor(-value % 24)} hours (total: {Math.floor(-value)} hours)</span>
        <div>
            <button className="btn btn-info" disabled={play} onClick={() => {
                onChange(min);
                setPlay(true);
            }}>
                <i className="fas fa-rotate-right"/>
            </button>
            <button className={play ? 'btn btn-warning' : 'btn btn-success'} onClick={() => setPlay(!play)}>
                <i className={play ? 'fas fa-pause' : 'fas fa-play'}/>
            </button>
            <button className="btn btn-danger" onClick={() => onChange(min)}>
                <i className={'fas fa-stop'}/>
            </button>
        </div>
    </div>
}
