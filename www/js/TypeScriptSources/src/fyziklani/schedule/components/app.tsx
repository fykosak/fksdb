import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import Powered from '../../../shared/powered';
import { IFyziklaniScheduleStore } from '../reducers/';
import { IData } from './index';
import Row from './row';

interface IState {
}

interface IProps {
    data: IData;
}

class Schedule extends React.Component<IState & IProps, {}> {

    public render() {
        const {data} = this.props;
        const rows = [];
        for (const blockName in data) {
            if (data.hasOwnProperty(blockName)) {
                const blockData = data[blockName];

                rows.push(<Row key={blockName} blockData={blockData} blockName={blockName}/>);
            }
        }

        return (
            <div style={{backgroundColor: '#f44336', color: 'white'}}>
                {rows}
                <Powered/>
            </div>
        );
    }
}

const mapStateToProps = (): IState => {
    return {};
};

const mapDispatchToProps = (dispatch: Dispatch<IFyziklaniScheduleStore>): IState => {
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(Schedule);
