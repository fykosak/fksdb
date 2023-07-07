import { axisBottom } from 'd3-axis';
import { scaleTime } from 'd3-scale';
import { select } from 'd3-selection';
import { SubmitModel } from 'FKSDB/Models/ORM/Models/Fyziklani/submit-model';
import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/team-model';
import * as React from 'react';
import { useSelector } from 'react-redux';
import './timeline.scss';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';

interface ExtendedSubmit extends SubmitModel {
    currentTeam: TeamModel;
}

interface OwnProps {
    taskId: number;
}

export default function Timeline({taskId}: OwnProps) {

    const taskSubmits: ExtendedSubmit[] = [];
    const start = useSelector((state: Store) => state.timer.gameStart);
    const submits = useSelector((state: Store) => state.data.submits);
    const teams = useSelector((state: Store) => state.data.teams);
    const end = useSelector((state: Store) => state.timer.gameEnd);

    const xScale = scaleTime().domain([start, end]).range([30, 580]);

    for (const index in submits) {
        if (Object.hasOwn(submits, index)) {
            const submit: SubmitModel = submits[index];
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
        return (new Date(a.modified)).getTime() - (new Date(b.modified)).getTime();
    });
    const dots = taskSubmits.filter((submit) => {
        const created = new Date(submit.modified);
        return created.getTime() > start.getTime() && created.getTime() < end.getTime();
    }).map((submit, index: number) => {

        const submitted = new Date(submit.modified);

        return <g style={{opacity: 1}} key={index}>
            <circle
                cx={xScale(submitted)}
                cy={50}
                r={5}
                style={{'--point-color': 'var(--color-fof-points-' + submit.points + ')'} as React.CSSProperties}
            ><title>
                {submit.currentTeam.name + '-' + submit.modified.toString()}
            </title>
            </circle>
        </g>;
    });
    return <div className="chart-game-task-timeline">
        <svg viewBox="0 0 600 100" className="chart ">
            <g transform="translate(0,70)" className="x axis"
               ref={(xAxisRef) => {
                   const xAxis = axisBottom(xScale);
                   select(xAxisRef).call(xAxis);
               }}/>
            {dots}
        </svg>
    </div>;
}
