import * as React from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import logger from 'redux-logger';
import { config } from '../../../config/';
import InputConnector from '../../../input-connector/compoenents/';
import { IEventAccommodation } from '../middleware/interfaces';
import { app } from '../reducer/';
import Matrix from './matrix/index';
import MultiNights from './multi-nights';
import Single from './single';

interface IProps {
    accommodationDef: IEventAccommodation[];
    input: HTMLInputElement;
    mode: 'matrix' | 'multiNights' | 'multiHotels' | 'single' | string;
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
            case 'single':
                return <Single accommodationDef={this.props.accommodationDef}/>;
            case 'multiNights':
                return <MultiNights accommodationDef={this.props.accommodationDef}/>;
            case 'multiHotels':
                throw new Error('Not implement');
            default:
                throw new Error('no match');
        }

    }
}
