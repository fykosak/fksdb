import { NetteActions } from '@appsCollector';
import { config } from '@config';
import { LangMap } from '@i18n/i18n';
import InputConnector from '@inputConnector/compoenents/';
import { Price } from '@shared/components/displays/price/interfaces';
import * as React from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import logger from 'redux-logger';
import { app } from '../reducers';
import App from './app';

interface Props {
    data: {
        data: Data;
        visible: boolean;
    };
    mode: string;
    actions: NetteActions;
    input: HTMLInputElement;
    description: string;
    label: string;
}

export type LocalizedParallelInfo = LangMap<LocalizedParallelItem>;

export interface InfoItem {
    description?: string;
    name: string;
}

export type LocalizedInfo = LangMap<InfoItem>;

export interface ChooserParallel extends LocalizedParallelInfo {
    price: Price;
    id: number;
}

export interface LocalizedParallelItem {
    description: string;
    name: string;
}

interface AbstractScheduleItem {
    date: {
        start: string;
        end: string;
    };
}

export interface ScheduleChooserItem extends AbstractScheduleItem {
    type: 'chooser';
    parallels: ChooserParallel[];
}

export interface ScheduleInfoItem extends AbstractScheduleItem {
    type: 'info';
    descriptions: LocalizedInfo;
}

export type ScheduleItem = ScheduleChooserItem | ScheduleInfoItem;

export interface Data {
    [key: string]: ScheduleItem;
}

export default class Index extends React.Component<Props, {}> {

    public render() {
        const store = config.dev ? createStore(app, applyMiddleware(logger)) : createStore(app);
        const {data, input, label, description} = this.props;
        // <CompactValue data={data}/>
        return (
            <Provider store={store}>
                <>
                    <InputConnector input={input}/>
                    <App
                        description={description}
                        label={label}
                        data={data}
                    />

                </>
            </Provider>
        );
    }
}
