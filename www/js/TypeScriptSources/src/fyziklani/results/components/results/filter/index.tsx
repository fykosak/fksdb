import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import { config } from '../../../../../config';
import { setNextFilter } from '../../../actions/table-filter';
import { IFyziklaniResultsStore } from '../../../reducers';

interface IState {
    autoSwitch?: boolean;
    onSetNextFilter?: () => any;
}

class AutoFilter extends React.Component<IState, {}> {

    public componentDidMount() {
        return this.scroll();
    }

    public render() {
        return null;
    }

    private async scroll() {
        if (this.props.autoSwitch) {
            await  window.scroll(0, 0);
            $(document).scrollTop(0);
            const {onSetNextFilter} = this.props;
            const documentHeight = $(document).height();
            const screenHeight = $(window).height() - 100;
            for (let i = 0; i <= Math.floor(documentHeight / screenHeight); i++) {
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
        return this.scroll();
    }
}

const mapDispatchToProps = (dispatch: Dispatch<IFyziklaniResultsStore>): IState => {
    return {
        onSetNextFilter: () => dispatch(setNextFilter()),
    };
};
const mapStateToPros = (state: IFyziklaniResultsStore): IState => {
    return {
        autoSwitch: state.tableFilter.autoSwitch,
    };
};

export default connect(
    mapStateToPros,
    mapDispatchToProps,
)(AutoFilter);
