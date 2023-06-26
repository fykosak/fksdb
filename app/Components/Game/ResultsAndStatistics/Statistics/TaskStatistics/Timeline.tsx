import { axisBottom } from 'd3-axis';
import { ScaleTime, scaleTime } from 'd3-scale';
import { select } from 'd3-selection';
import { SubmitModel, Submits } from 'FKSDB/Models/ORM/Models/Fyziklani/SubmitModel';
import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TeamModel';
import * as React from 'react';
import { connect } from 'react-redux';
import './timeline.scss';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';

interface StateProps {
    submits: Submits;
    teams: TeamModel[];
    fromDate: Date;
    toDate: Date;
}

interface ExtendedSubmit extends SubmitModel {
    currentTeam: TeamModel;
}

interface OwnProps {
    taskId: number;
}

class Timeline extends React.Component<StateProps & OwnProps, never> {

    private xAxis: SVGGElement;

    private xScale: ScaleTime<number, number>;

    public componentDidMount() {
        this.getAxis();
    }

    public componentDidUpdate() {
        this.getAxis();
    }

    public render() {
        const taskSubmits: ExtendedSubmit[] = [];
        const {
            taskId,
            submits,
            teams,
            fromDate,
            toDate,
        } = this.props;

        this.xScale = scaleTime().domain([fromDate, toDate]).range([30, 580]);

        for (const index in submits) {
            if (Object.hasOwn(submits,index)) {
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
            return created.getTime() > fromDate.getTime() && created.getTime() < toDate.getTime();
        }).map((submit, index: number) => {

            const submitted = new Date(submit.modified);

            return (
                <g style={{opacity: 1}} key={index}>
                    <circle
                        cx={this.xScale(submitted)}
                        cy={50}
                        r={5}
                        fill={'var(--color-fof-points-' + submit.points + ')'}
                    ><title>
                        {submit.currentTeam.name + '-' + submit.modified.toString()}
                    </title>
                    </circle>
                </g>
            );
        });
        return (
            <div className="chart-game-task-timeline">
                <svg viewBox="0 0 600 100" className="chart ">
                    <g transform="translate(0,70)" className="x axis"
                       ref={(xAxis) => this.xAxis = xAxis}/>
                    {dots}
                </svg>
            </div>
        );
    }

    private getAxis() {
        const xAxis = axisBottom(this.xScale);
        select(this.xAxis).call(xAxis);
    }
}

const mapStateToProps = (state: Store): StateProps => {
    return {
        fromDate: state.timer.gameStart,
        submits: state.data.submits,
        teams: state.data.teams,
        toDate: state.timer.gameEnd,
    };
};

export default connect(mapStateToProps, null)(Timeline);
