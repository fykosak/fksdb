import * as React from 'react';
import * as ReactDOM from 'react-dom';

interface ITaskCodeProps {
    node: HTMLInputElement;
    tasks: Array<any>;
    teams: Array<any>;
}

interface ITaskCodeState {
    task: string;
    team: number;
    validTask: boolean;
    control: number;
    validTeam: boolean;
    valid: boolean;
}

export default class TaskCode extends React.Component<ITaskCodeProps,ITaskCodeState> {
    private controlInput;
    private teamInput;
    private taskInput;

    constructor() {
        super();
        this.state = {
            task: undefined,
            team: undefined,
            control: undefined,
            validTask: false,
            validTeam: false,
            valid: false,
        }
    }

    public componentDidMount() {
        // focus on load
        jQuery(ReactDOM.findDOMNode(this.teamInput)).focus();
    }

    public render() {
        const onInputTask = (event) => {
            const value = event.target.value.toLocaleUpperCase();
            const oldValue = this.state.task;
            if (value === oldValue) {
                return;
            }
            const valid = this.isValid(this.getFullCode(null, value, null));
            const validTask = this.isValidTask(value);
            if (validTask) {
                jQuery(ReactDOM.findDOMNode(this.controlInput)).focus();
            }
            this.setState(
                {
                    task: value,
                    validTask,
                    valid,
                }
            );
        };

        const onInputTeam = (event) => {
            const value = +event.target.value;
            const oldValue = this.state.team;
            if (value === oldValue) {
                return;
            }
            const valid = this.isValid(this.getFullCode(value, null, null));
            const validTeam = this.isValidTeam(value);
            if (validTeam) {
                jQuery(ReactDOM.findDOMNode(this.taskInput)).focus();
            }
            this.setState({
                team: value,
                valid,
                validTeam,
            });
        };

        const onInputControl = (event) => {
            if (event.target.value === "") {
                return;
            }
            const value = +event.target.value;
            const oldValue = this.state.control;
            if (value === oldValue) {
                return;
            }
            const valid = this.isValid(this.getFullCode(null, null, value));
            this.setState({
                control: value,
                valid,
            });
        };

        return (
            <div
                className={'task-code-container'}>
                <div
                    className={'form-control has-feedback '}>
                    <input
                        maxLength={6}
                        ref={(input)=>this.teamInput = input}
                        className={'team '+(this.state.validTeam===false?'invalid':(this.state.validTeam===true?'valid':''))}
                        onKeyUp={ onInputTeam }
                        placeholder="XXXXXX"
                    />
                    <input
                        maxLength={2}
                        className={'task '+(this.state.validTask===false?'invalid':(this.state.validTask===true?'valid':''))}
                        ref={(input)=>this.taskInput = input}
                        placeholder="XX"
                        onKeyUp={onInputTask}
                    />
                    <input
                        maxLength={1}
                        ref={(input)=>this.controlInput = input}
                        className={'control '+(this.state.valid?'valid':'invalid')}
                        placeholder="X"
                        onKeyUp={onInputControl}
                    />
                    <span
                        className={'glyphicon '+( this.state.valid? 'glyphicon-ok':'') + ' form-control-feedback'}
                    />
                </div>
            </div>
        );
    };

    private getFullCode(team: number, task: string, control: number): string {

        const teamString = team || (+this.state.team < 1000) ? '0' + +this.state.team : +this.state.team;
        const taskString = task || this.state.task || '';
        const controlString = (control !== undefined) ? control : (this.state.control);
        return '00' + teamString + taskString + controlString;
    }

    private isValid(code: string): boolean {
        let {validTeam, validTask}= this.state;
        if (!validTask) {
            return false;
        }
        if (!validTeam) {
            return false;
        }
        const subCode = code.split('').map((char): number => {
            return +char.toLocaleUpperCase()
                .replace(/A/, '1')
                .replace(/B/, '2')
                .replace(/C/, '3')
                .replace(/D/, '4')
                .replace(/E/, '5')
                .replace(/F/, '6')
                .replace(/G/, '7')
                .replace(/H/, '8');
        });
        const c = this.getControl(subCode);
        return (c % 10 == 0);
    }

    private isValidTask(task: string): boolean {
        const {tasks} = this.props;
        return tasks.map(task => task.label).indexOf(task) !== -1;
    }

    private isValidTeam(team: number): boolean {
        const {teams} = this.props;
        return teams.map(team => team.team_id).indexOf(+team) !== -1;
    }

    public componentDidUpdate(): void {
        this.props.node.value = '';
        if (this.state.valid) {
            this.props.node.value = this.getFullCode(null, null, null);
        }
    }

    private getControl(subCode: Array<any>): number {
        return 3 * (+subCode[0] + +subCode[3] + +subCode[6]) +
            7 * (+subCode[1] + +subCode[4] + +subCode[7]) +
            (+subCode[2] + +subCode[5] + +subCode[8])
    }
}
