import * as React from 'react';
import * as ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import logger from 'redux-logger';
import { config } from '../../config/';
import { IAccommodationItem } from '../middleware/interfaces';
import { app } from '../reducer/';
import Accommodation from './accommodation';

interface IProps {
    accommodationDef: IAccommodationItem[];
}

class Index extends React.Component<IProps, {}> {

    public render() {
        const store = !config.dev ? createStore(app, applyMiddleware(logger)) : createStore(app);

        return (
            <Provider store={store}>
                <Accommodation accommodationDef={this.props.accommodationDef}/>
            </Provider>
        );
    }
}

document.querySelectorAll('[data-id=person-accommodation-matrix]').forEach((el: Element) => {
    const accommodationDef = JSON.parse(el.getAttribute('data-accommodation-def'));
    ReactDOM.render(<Index accommodationDef={accommodationDef}/>, el.parentElement.parentElement);
});
