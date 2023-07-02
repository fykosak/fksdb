import { scaleLinear, scaleTime } from 'd3-scale';
import { curveLinear } from 'd3-shape';
import LineChart from 'FKSDB/Components/Charts/Core/LineChart/line-chart';
import { ExtendedPointData, LineChartData } from 'FKSDB/Components/Charts/Core/LineChart/middleware';
import { SubmitModel, Submits } from 'FKSDB/Models/ORM/Models/Fyziklani/SubmitModel';
import { TaskModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TaskModel';
import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TeamModel';
import * as React from 'react';
import { connect } from 'react-redux';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';

interface StateProps {
    submits: Submits;
    tasks: TaskModel[];
    gameStart: Date;
    gameEnd: Date;
    activePoints: number;
    teams: TeamModel[];
}

interface OwnProps {
    teamId: number;
}

class PointsInTime extends React.Component<StateProps & OwnProps, never> {

    public render() {
        const {
            teamId,
            submits,
            tasks,
            activePoints,
            gameEnd,
            gameStart,
            teams,
        } = this.props;

        const teamSubmits: ExtendedPointData<Date>[] = [];

        let maxPoints = 0;
        let meanPoints = 0;
        const numberOfTeams = teams.length;
        const meanTeamData: ExtendedPointData<Date>[] = [];
        const lineChartData: LineChartData<Date> = [];

        for (const index in submits) {
            if (Object.hasOwn(submits,index)) {
                const submit: SubmitModel = submits[index];
                const {teamId: submitTeamId, points} = submit;
                meanPoints += (submit.points / numberOfTeams);
                meanTeamData.push({
                    active: false,
                    color: {
                        active: null,
                        inactive: null,
                    },
                    xValue: new Date(submit.modified),
                    yValue: meanPoints,
                });

                if (teamId === submitTeamId) {
                    const currentTask = tasks.filter((task) => {
                        return submit.taskId === task.taskId;
                    })[0];
                    if (points !== null && points !== 0) {
                        maxPoints += +points;
                        teamSubmits.push({
                            active: (!(activePoints && (activePoints !== submit.points))),
                            color: {
                                active: 'var(--color-fof-points-' + submit.points + ')',
                                inactive: 'var(--color-fof-points-' + submit.points + ')',
                            },
                            label: currentTask.label,
                            xValue: new Date(submit.modified),
                            yValue: maxPoints,
                        });
                    }
                }
            }
        }

        const xScale = scaleTime<number, number>().domain([gameStart, gameEnd]);
        const yScale = scaleLinear<number, number>().domain([0, Math.max(maxPoints, meanPoints)]);

        lineChartData.push({
            color: '#ccc',
            display: {
                area: false,
                lines: true,
                points: false,
            },
            name: 'mean team',
            points: [
                {
                    active: false,
                    color: {
                        active: null,
                        inactive: null,
                    },
                    xValue: gameStart,
                    yValue: 0,
                },
                ...meanTeamData,
            ],
        });
        lineChartData.push({
            color: '#1175da',
            curveFactory: curveLinear,
            display: {
                area: false,
                lines: true,
                points: true,
            },
            name: 'TeamId ' + teamId,
            points: [
                {
                    active: false,
                    xValue: gameStart,
                    yValue: 0,
                    color: {
                        active: null,
                        inactive: null,
                    },
                },
                ...teamSubmits,
                {
                    active: false,
                    xValue: gameEnd,
                    yValue: maxPoints,
                    color: {
                        active: null,
                        inactive: null,
                    },
                },
            ],
        });
        return <LineChart<Date> data={lineChartData} xScale={xScale} yScale={yScale}/>;
    }
}

const mapStateToProps = (state: Store): StateProps => {
    return {
        activePoints: state.statistics.activePoints,
        gameEnd: new Date(state.timer.gameEnd),
        gameStart: new Date(state.timer.gameStart),
        submits: state.data.submits,
        tasks: state.data.tasks,
        teams: state.data.teams,
    };
};

export default connect(mapStateToProps, null)(PointsInTime);
