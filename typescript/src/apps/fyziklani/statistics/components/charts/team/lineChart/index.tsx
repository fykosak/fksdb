import { LineChartData } from '@shared/components/lineChart/interfaces';
import LineChart from '@shared/components/lineChart/lineChart';
import {
    scaleLinear, scaleTime,
} from 'd3-scale';
import { curveLinear } from 'd3-shape';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Submit,
    Submits,
    Task,
    Team,
} from '../../../../../helpers/interfaces';
import { getColorByPoints } from '../../../../middleware/charts/colors';
import { Store as StatisticsStore } from '../../../../reducers';

interface StateProps {
    submits: Submits;
    tasks: Task[];
    gameStart: Date;
    gameEnd: Date;
    activePoints: number;
    teams: Team[];
}

interface OwnProps {
    teamId: number;
}

class PointsInTime extends React.Component<StateProps & OwnProps, {}> {

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

        const teamSubmits: Array<{
            active: boolean;
            color: string;
            xValue: Date;
            yValue: number;
            label: string;
        }> = [];

        let maxPoints = 0;
        let meanPoints = 0;
        const numberOfTeams = teams.length;
        const meanTeamData: Array<{
            yValue: number;
            xValue: Date;
        }> = [];
        const lineChartData: LineChartData = [];

        for (const index in submits) {
            if (submits.hasOwnProperty(index)) {
                const submit: Submit = submits[index];
                const {teamId: submitTeamId, points} = submit;
                meanPoints += (submit.points / numberOfTeams);
                meanTeamData.push({
                    xValue: new Date(submit.created),
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
                            color: getColorByPoints(submit.points),
                            label: currentTask.label,
                            xValue: new Date(submit.created),
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
                    xValue: gameStart,
                    yValue: 0,
                },
                ...teamSubmits,
                {
                    xValue: gameEnd,
                    yValue: maxPoints,
                },
            ],
        });
        return <LineChart data={lineChartData} xScale={xScale} yScale={yScale}/>;
    }
}

const mapStateToProps = (state: StatisticsStore): StateProps => {
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
