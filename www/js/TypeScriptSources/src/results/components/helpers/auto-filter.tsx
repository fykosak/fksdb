import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import { config } from '../../../config';
import { setNextFilter } from '../../actions/table-filter';
import { IStore } from '../../reducers';

interface IState {
    autoSwitch?: boolean;
    onSetNextFilter?: () => any;
}

class Clock extends React.Component<IState, {}> {
    public componentDidMount() {
        this.scroll();
    }

    public render() {
        return null;
    }

    private async scroll() {
        if (this.props.autoSwitch) {
            await  window.scroll(0, 0);
            /* await new Promise<void>((resolve) => {
                 $('html, body').animate({
                     scrollTop: 0,
                 }, config.filterDelay / 3, "linear", () => {
                     resolve();
                 });
             });*/
            $(document).scrollTop(0);
            const {onSetNextFilter} = this.props;
            const documentHeight = $(document).height();
            const screenHeight = $(window).height() - 100;
            for (let i = 0; i <= Math.floor(documentHeight / screenHeight); i++) {
                /*await new Promise<void>((resolve) => {
                    $('html, body').animate({
                        scrollTop: i * screenHeight,
                    }, config.filterDelay / 3, "linear", () => {
                        resolve();
                    });
                });*/
                await window.scroll(0, i * screenHeight);

                await new Promise<void>((resolve) => {
                    setTimeout(() => {
                        resolve();
                    }, config.filterDelay);
                });
            }
            onSetNextFilter();
        } else {
            await new Promise<void>((resolve) => {
                setTimeout(() => {
                    resolve();
                }, config.filterDelay);
            });
        }
        this.scroll();
    }
}

const mapDispatchToProps = (dispatch: Dispatch<IStore>): IState => {
    return {
        onSetNextFilter: () => dispatch(setNextFilter()),
    };
};
const mapStateToPros = (state: IStore): IState => {
    return {
        autoSwitch: state.tableFilter.autoSwitch,
    };
};

export default connect(
    mapStateToPros,
    mapDispatchToProps,
)(Clock);
