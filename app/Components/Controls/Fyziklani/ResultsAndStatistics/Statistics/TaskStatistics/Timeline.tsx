import { axisBottom } from 'd3-axis';
import {
    ScaleTime,
    scaleTime,
} from 'd3-scale';
import { select } from 'd3-selection';
import { ModelFyziklaniSubmit, Submits } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniSubmit';
import { ModelFyziklaniTeam } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniTeam';
import * as React from 'react';
import { connect } from 'react-redux';
import { FyziklaniStatisticStore } from '../Reducers';
import './timeline.scss';

interface StateProps {
    submits: Submits;
    teams: ModelFyziklaniTeam[];
    fromDate: Date;
    toDate: Date;
}

interface ExtendedSubmit extends ModelFyziklaniSubmit {
    currentTeam: ModelFyziklaniTeam;
}

interface OwnProps {
    taskId: number;
}

class Timeline extends React.Component<StateProps & OwnProps> {

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
            if (submits.hasOwnProperty(index)) {
                const submit: ModelFyziklaniSubmit = submits[index];
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
            return (new Date(a.created)).getTime() - (new Date(b.created)).getTime();
        });
        const dots = taskSubmits.filter((submit) => {
            const created = new Date(submit.created);
            return created.getTime() > fromDate.getTime() && created.getTime() < toDate.getTime();
        }).map((submit, index: number) => {

            const submitted = new Date(submit.created);

            return (
                <g style={{opacity: 1}} key={index}>
                    <circle
                        cx={this.xScale(submitted)}
                        cy={50}
                        r={5}
                        data-points={submit.points}
                    ><title>
                        {submit.currentTeam.name + '-' + submit.created.toString()}
                    </title>
                    </circle>
                </g>
            );
        });
        return (
            <div className="col-lg-12">
                <svg viewBox="0 0 600 100" className="chart chart-fyziklani-task-timeline">
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

const mapStateToProps = (state: FyziklaniStatisticStore): StateProps => {
    return {
        fromDate: state.timer.gameStart,
        submits: state.data.submits,
        teams: state.data.teams,
        toDate: state.timer.gameEnd,
    };
};

export default connect(mapStateToProps, null)(Timeline);