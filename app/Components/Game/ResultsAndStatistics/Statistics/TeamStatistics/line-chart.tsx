import { scaleLinear, scaleTime } from 'd3-scale';
import { curveLinear } from 'd3-shape';
import LineChart from 'FKSDB/Components/Charts/Core/LineChart/line-chart';
import { ExtendedPointData, LineChartData } from 'FKSDB/Components/Charts/Core/LineChart/middleware';
import { SubmitModel } from 'FKSDB/Models/ORM/Models/Fyziklani/submit-model';
import * as React from 'react';
import { useSelector } from 'react-redux';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';

interface OwnProps {
    teamId: number;
}

export default function PointsInTime({teamId}: OwnProps) {
    const gameEnd = new Date(useSelector((state: Store) => state.timer.gameEnd));
    const gameStart = new Date(useSelector((state: Store) => state.timer.gameStart));
    const submits = useSelector((state: Store) => state.data.submits);
    const tasks = useSelector((state: Store) => state.data.tasks);
    const teams = useSelector((state: Store) => state.data.teams);

    const teamSubmits: ExtendedPointData<Date>[] = [];

    let maxPoints = 0;
    let meanPoints = 0;
    const numberOfTeams = teams.length;
    const meanTeamData: ExtendedPointData<Date>[] = [];
    const lineChartData: LineChartData<Date> = [];

    const submitArray: SubmitModel[] = Object.values(submits);
    submitArray
        .sort((a, b) => (new Date(a.modified)).getTime() - (new Date(b.modified)).getTime())
        .forEach((submit) => {
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
                        active: false,
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
        });

    const xScale = scaleTime<number, number>().domain([gameStart, gameEnd]);
    const yScale = scaleLinear<number, number>().domain([0, Math.max(maxPoints, meanPoints)]);

    lineChartData.push({
        color: 'var(--bs-gray-400)',
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
        color: 'var(--bs-gray-600)',
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
