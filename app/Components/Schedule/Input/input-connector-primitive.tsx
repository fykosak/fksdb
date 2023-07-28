import { setInitialData } from 'vendor/fykosak/nette-frontend-component/src/InputConnector/actions';
import * as React from 'react';
import { useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { InputConnectorStateMap } from 'vendor/fykosak/nette-frontend-component/src/InputConnector/reducer';

export interface OwnProps {
    input: HTMLInputElement | HTMLSelectElement;
}

export default function InputConnectorPrimitive({input} : OwnProps) {

    const value = useSelector((state: { inputConnector: InputConnectorStateMap }) => +state.inputConnector?.data?.data);
    const dispatch = useDispatch();
    useEffect(() => {
        if (input.value) {
            dispatch(setInitialData({data: +input.value}))
        }
    }, []);
    useEffect(() => {
        input.value = value ? value.toString() : null;
        input.dispatchEvent(new Event('change'));
    }, [value]);
    return null;
}
