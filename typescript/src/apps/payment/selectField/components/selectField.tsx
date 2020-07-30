import InputConnector from '@inputConnector/netteInputConnector';
import StoreCreator from '@shared/components/storeCreator';
import * as React from 'react';
import { PaymentScheduleItem } from '../interfaces';
import { app } from '../reducer';
import Container from './container';

interface OwnProps {
    data: PaymentScheduleItem[];
    input: Element;
}

export default class SelectField extends React.Component<OwnProps, {}> {

    public render() {
        const {input, data} = this.props;
        if (!(input instanceof HTMLInputElement)) {
            return false;
        }
        input.style.display = 'none';
        return <StoreCreator app={app}>
            <>
                <InputConnector input={input}/>
                <Container items={data}/>
            </>
        </StoreCreator>;
    }
}
