import * as React from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import logger from 'redux-logger';
import { INetteActions } from '../../../app-collector/';
import { config } from '../../../config';
import { app } from '../../../events/accommodation/reducer';
import { IPrice } from '../../../shared/components/displays/price/interfaces';
import App from './app';

interface IProps {
    mode: string;
    actions: INetteActions;
    input: HTMLInputElement;
}

export interface IScheduleItem {
    description: string;
    id: number;
    name: string;
    place: string;
    price: IPrice;
}

export interface IData {
    [key: string]: {
        date: {
            start: string;
            end: string;
        };
        parallels: IScheduleItem[];
    };
}

export default class Index extends React.Component<IProps, {}> {

    public render() {
        const store = config.dev ? createStore(app, applyMiddleware(logger)) : createStore(app);

        const data: IData = {
            block1: {
                date: {
                    end: '1994-05-15 00:00:00',
                    start: '1994-05-15 00:00:00',
                },
                parallels: [
                    {
                        description: '',
                        id: 0,
                        name: '',
                        place: '',
                        price: {kc: 0, eur: 0},
                    },
                ],
            },
        };
        return (
            <Provider store={store}>
                <>
                    <App data={data}/>
                </>
            </Provider>
        );
        // <InputConnector input={this.props.input}/>
    }
}
