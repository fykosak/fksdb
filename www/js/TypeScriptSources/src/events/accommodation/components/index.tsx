import * as React from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import logger from 'redux-logger';
import { config } from '../../../config/';
import { IEventAccommodation } from '../middleware/interfaces';
import { app } from '../reducer/';
import Matrix from './matrix/index';
import InputConnector from './input-connector';
import Boolean from './boolean';

interface IProps {
    accommodationDef: IEventAccommodation[];
    input: HTMLInputElement;
    mode: 'matrix' | 'multiNight' | 'multiHotels' | 'boolean' | string;
}

export default class Index extends React.Component<IProps, {}> {

    public render() {
        const store = config.dev ? createStore(app, applyMiddleware(logger)) : createStore(app);

        return (
            <Provider store={store}>
                <>
                    <InputConnector input={this.props.input}/>
                    {this.getComponentByMode()}
                </>
            </Provider>
        );
    }

    private getComponentByMode(): JSX.Element {
        switch (this.props.mode) {
            case 'matrix':
                return <Matrix accommodationDef={this.props.accommodationDef}/>;
            case 'multiNight':
            case 'multiHotels':
            case 'boolean':
                return <Boolean accommodationDef={this.props.accommodationDef}/>
            default:
                throw new Error('no match');
        }

    }
}
