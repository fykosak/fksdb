import * as React from 'react';
import * as ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import logger from 'redux-logger';
import PersonProvider from './components/provider';
import { app } from './reducers';

interface IProps {
    html: string;
    hasErrors: boolean;
    legend: string;
}

class App extends React.Component<IProps, {}> {
    public render() {
        const store = createStore(app, applyMiddleware(logger));
        return (
            <Provider store={store}>
                <PersonProvider html={this.props.html} hasErrors={this.props.hasErrors} legend={this.props.legend}/>
            </Provider>
        );
    }
}

document.querySelectorAll('*[data-referenced]').forEach((element: HTMLDivElement) => {

    const hasErrors = !!element.querySelectorAll('.has-error').length;

    const elClear = element.querySelector('input[type=submit][name*=__clear]');
    if (elClear) {
        elClear.parentElement.parentElement.remove();
    }
    const legendElement = element.querySelector('legend');
    let legend = null;
    if (legendElement) {
        legend = legendElement.innerText;
    }
    const compactValueElement = element.querySelector('input[name*=_c_compact]');
    let html = null;
    if (compactValueElement) {
        html = element.innerHTML;
    }
    ReactDOM.render(<App html={html} hasErrors={hasErrors} legend={legend}/>, element);
});
