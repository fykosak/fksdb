import * as React from 'react';
import { connect } from 'react-redux';
import { IFyziklaniScheduleStore } from '../reducers';
import { IData } from './index';
import Row from './row';

interface IProps {
    data: IData;
}

interface IState {
    showChooser?: boolean;
}

class Schedule extends React.Component<IProps & IState, {}> {

    public render() {
        const {data, showChooser} = this.props;
        const rows = [];
        for (const blockName in data) {
            if (data.hasOwnProperty(blockName)) {
                const blockData = data[blockName];
                rows.push(<Row key={blockName} blockData={blockData} blockName={blockName}/>);
            }
        }

        return (
            <div className={'bd-callout bd-callout-fyziklani'} style={{display: showChooser ? '' : 'none'}}>
                <div className={'schedule-field-container'}>
                    {rows}
                </div>
            </div>
        );
    }
}

const mapStateToProps = (store: IFyziklaniScheduleStore): IState => {
    return {
        showChooser: store.compactValue.showChooser,
    };
};

const mapDispatchToProps = (): IState => {
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(Schedule);
