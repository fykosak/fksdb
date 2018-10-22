import * as React from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import logger from 'redux-logger';
import { INetteActions } from '../../../app-collector/';
import { config } from '../../../config';
import { ILangMap } from '../../../i18n/i18n';
import InputConnector from '../../../input-connector/compoenents';
import { IPrice } from '../../../shared/components/displays/price/interfaces';
import { app } from '../reducers';
import App from './app';
import CompactValue from './compact-value';

interface IProps {
    data: IData;
    mode: string;
    actions: INetteActions;
    input: HTMLInputElement;
}

export type ILocalizedParallelInfo = ILangMap<ILocalizedParallelItem>;

export interface IInfoItem {
    description?: string;
    name: string;
}

export type ILocalizedInfo = ILangMap<IInfoItem>;

export interface IChooserParallel extends ILocalizedParallelInfo {
    price: IPrice;
    id: number;
}

export interface ILocalizedParallelItem {
    description: string;
    name: string;
    place: string;
}

interface IAbstractScheduleItem {
    date: {
        start: string;
        end: string;
    };
}

export interface IScheduleChooserItem extends IAbstractScheduleItem {
    type: 'chooser';
    parallels: IChooserParallel[];
}

export interface IScheduleInfoItem extends IAbstractScheduleItem {
    type: 'info';
    descriptions: ILocalizedInfo;
}

export type IScheduleItem = IScheduleChooserItem | IScheduleInfoItem;

export interface IData {
    [key: string]: IScheduleItem;
}

export default class Index extends React.Component<IProps, {}> {

    public render() {
        const store = !config.dev ? createStore(app, applyMiddleware(logger)) : createStore(app);
        const {data, input} = this.props;
        return (
            <Provider store={store}>
                <>
                    <CompactValue data={data}/>
                    <InputConnector input={input}/>
                    <App data={data}/>

                </>
            </Provider>
        );
    }
}
