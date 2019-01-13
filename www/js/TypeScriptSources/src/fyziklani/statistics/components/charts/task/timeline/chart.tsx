import { axisBottom } from 'd3-axis';
import {
    ScaleTime,
    scaleTime,
} from 'd3-scale';
import { select } from 'd3-selection';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    ISubmit,
    ISubmits,
    ITeam,
} from '../../../../../helpers/interfaces';
import { getColorByPoints } from '../../../../middleware/charts/colors';
import { IFyziklaniStatisticsStore } from '../../../../reducers';

interface IState {
    submits?: ISubmits;
    teams?: ITeam[];
    fromDate?: Date;
    toDate?: Date;
}

interface IExtendedSubmit extends ISubmit {
    currentTeam: ITeam;
}

interface IProps {
    taskId: number;
}

class Timeline extends React.Component<IState & IProps, {}> {

    private xAxis: SVGGElement;

    private xScale: ScaleTime<number, number>;

    public componentDidMount() {
        this.getAxis();
    }

    public componentDidUpdate() {
        this.getAxis();
    }

    public render() {
        const taskSubmits: IExtendedSubmit[] = [];
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
            return (new Date(a.created)).getTime() - (new Date(b.created)).getTime();
        });
        const dots = taskSubmits.filter((submit) => {
            const created = new Date(submit.created);
            return created.getTime() > fromDate.getTime() && created.getTime() < toDate.getTime();
        }).map((submit, index: number) => {

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
        const xAxis = axisBottom(this.xScale);
        select(this.xAxis).call(xAxis);
    }
}

const mapStateToProps = (state: IFyziklaniStatisticsStore): IState => {
    return {
        fromDate: state.statistics.fromDate,
        submits: state.data.submits,
        teams: state.data.teams,
        toDate: state.statistics.toDate,
    };
};

export default connect(mapStateToProps, null)(Timeline);
