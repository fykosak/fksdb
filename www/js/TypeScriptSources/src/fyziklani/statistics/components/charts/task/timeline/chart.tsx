import * as d3 from 'd3';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    ISubmit,
    ISubmits,
} from '../../../../../../shared/interfaces';
import { getColorByPoints } from '../../../../middleware/charts/colors';
import { IFyziklaniStatisticsStore } from '../../../../reducers';
import { ITeam } from '../../../../../helpers/interfaces';

interface IState {
    submits?: ISubmits;
    teams?: ITeam[];
    taskId?: number;
    gameStart?: Date;
    gameEnd?: Date;
}

interface IExtendedSubmit extends ISubmit {
    currentTeam: ITeam;
}

class Timeline extends React.Component<IState, {}> {

    private xAxis: any;

    private xScale: d3.ScaleTime<number, number>;

    public componentDidMount() {
        this.getAxis();
    }

    public componentDidUpdate() {
        this.getAxis();
    }

    public render() {
        const taskSubmits: IExtendedSubmit[] = [];
        const {
            gameStart,
            gameEnd,
            taskId,
            submits,
            teams,
        } = this.props;

        this.xScale = d3.scaleTime().domain([gameStart, gameEnd]).range([30, 580]);

        for (const index in submits) {
            if (submits.hasOwnProperty(index)) {
                const submit: ISubmit = submits[index];
                if (submit.taskId === taskId) {
                    const currentTeam = teams.filter((team) => {
                        return submit.teamId === team.teamId;
                    });
                    if (currentTeam.length) {
                        taskSubmits.push({
                            ...submit,
                            currentTeam: currentTeam[0],
                        });
                    }
                }
            }
        }
        taskSubmits.sort((a, b) => {
            return a.created - b.created;
        });
        const dots = taskSubmits.map((submit, index: number) => {

            const submitted = new Date(submit.created);
            const color = getColorByPoints(submit.points);

            return (
                <g style={{opacity: 1}} key={index}>
                    <circle
                        cx={this.xScale(submitted)}
                        cy={50}
                        r={5}
                        fill={color}
                        stroke={'white'}
                        strokeWidth={1}
                    ><title>
                        {submit.currentTeam.name + '-' + submit.created.toString()}
                    </title>
                    </circle>
                </g>
            );
        });
        return (
            <div className="col-lg-12">
                <svg viewBox={'0 0 600 100'} className="chart time-line">
                    <g transform={'translate(0,70)'} className="x axis"
                       ref={(xAxis) => this.xAxis = xAxis}/>
                    {dots}
                </svg>
            </div>
        );
    }

    private getAxis() {
        const xAxis = d3.axisBottom(this.xScale);
        d3.select(this.xAxis).call(xAxis);
    }
}

const mapStateToProps = (state: IFyziklaniStatisticsStore): IState => {
    return {
        gameEnd: new Date(state.timer.gameEnd),
        gameStart: new Date(state.timer.gameStart),
        submits: state.data.submits,
        taskId: state.statistics.taskId,
        teams: state.data.teams,
    };
};

const mapDispatchToProps = (): IState => {
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(Timeline);

/*
 <text
 x={(fromCoordinates + toCoordinates) / 2}
 y={yCoordinates - 1}
 fontSize="10"
 textAnchor="middle"
 >
 {task.label}
 </text>*/
