import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import Powered from '../../shared/components/powered';
import {
    IRoom,
    ITask,
    ITeam,
} from '../../shared/interfaces';
import {
    setInitialParameters,
} from '../actions/downloader';
import { IStore } from '../reducers/';
import BrawlDashboard from './dashboard';
import Downloader from './helpers/downloader';

export interface IParams {
    gameStart: string;
    gameEnd: string;
    basePath: string;
}

interface IProps {
    params: IParams;
    tasks: ITask[];
    teams: ITeam[];
    rooms: IRoom[];
}

interface IState {
    onSetInitialParams?: (rooms: IRoom[], tasks: ITask[], teams: ITeam[], params: IParams) => void;
}

class BrawlApp extends React.Component<IProps & IState, {}> {
    public componentDidMount() {
        const {tasks, teams, rooms, onSetInitialParams, params} = this.props;
        onSetInitialParams(rooms, tasks, teams, params);
    }

    public render() {
        return (
            <div>
                <Downloader/>
                <BrawlDashboard basePath={this.props.params.basePath}/>
                <Powered/>
            </div>
        );
    }
}

export default connect((): IState => {
    return {};
}, (dispatch: Dispatch<IStore>): IState => {
    return {
        onSetInitialParams: (rooms, tasks, teams, params) => dispatch(setInitialParameters(rooms, tasks, teams, params)),
    };
})(BrawlApp);
