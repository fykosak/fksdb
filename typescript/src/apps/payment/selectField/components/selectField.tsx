import StoreCreator from '@shared/components/storeCreator';
import InputConnector from '@inputConnector/compoenents/';
import * as React from 'react';
import { PaymentScheduleItem } from '../interfaces';
import { app } from '../reducer/';
import Container from './container';

interface OwnProps {
    items: PaymentScheduleItem[];
    input: HTMLInputElement;
}

export default class SelectField extends React.Component<OwnProps, {}> {

    public render() {
        return <StoreCreator app={app}>
            <>
                <InputConnector input={this.props.input}/>
                <Container items={this.props.items}/>
            </>
        </StoreCreator>;
    }
}
