import * as React from 'react';
import {
    ISubmit,
    ISubmits,
    ITask,
    ITeam,
} from '../../../helpers/interfaces';
import { Filter } from './filter/filter';
import { getColorByPoints } from '../../../statistics/middleware/charts/colors';

interface IState {
    filter?: Filter;
    submits?: ISubmits;
    teams?: ITeam[];
    tasks?: ITask[];
    cols?: number;
    rows?: number;
    position?: number;
}

interface IItem {
    teamId: number;
    submits: {
        [taskId: number]: ISubmit;
    };
    points: number;
    groups: {
        1: number;
        2: number;
        3: number;
        5: number;
    };
    count: number;
}

interface IProps {
    item: IItem;
    position: number;
    team: ITeam;
    availablePoints: number[];
}

export default class TeamPresentationRow extends React.Component<IProps, {}> {
    public render() {
        const {item, position, team, availablePoints} = this.props;

        const average = item.count > 0 ? Math.round(item.points / item.count * 100) / 100 : '-';
        return <div className={'row'} key={item.teamId}>
            <div className={'col-1'}>{position}.</div>
            <div className={'col-4'}>{team.name}</div>
            <div className={'col-1'}>{item.points}</div>
            <div className={'col-1'}>{item.count}</div>
            <div className={'col-1'}>{average}</div>
            <div className={'col-4'}>
                <div className="progress">
                    {availablePoints.map((points) => {
                        const width = ((item.groups[points]) ?
                            (Math.round(item.groups[points] / item.count * 100)) :
                            0) + '%';
                        return <div key={points} className="progress-bar" style={{
                            backgroundColor: getColorByPoints(points),
                            width,
                        }}/>;
                    })}
                </div>
            </div>
        </div>;
    }

}
