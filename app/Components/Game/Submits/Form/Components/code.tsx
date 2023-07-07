import * as React from 'react';
import { useEffect, useRef } from 'react';
import { WrappedFieldProps } from 'redux-form';

export default function Code(props: WrappedFieldProps) {

    const {meta: {valid, active}, input} = props;
    const inputRef = useRef<HTMLInputElement>(null);
    useEffect(() => {
        if (active) {
            inputRef.current?.focus();
        }
    }, [active, inputRef]);
    return <span className={'form-group ' + (valid ? 'has-success' : 'has-error')}>
        <input
            ref={inputRef}
            {...input}
            maxLength={9}
            className={'form-control-lg form-control ' + (valid ? 'is-valid' : 'is-invalid')}
            placeholder="XXXXXXYYX"
            autoFocus
        />
    </span>;
}
