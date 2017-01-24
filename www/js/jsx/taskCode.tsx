class TaskCode extends React.Component<any,any> {
    constructor() {
        super();
        this.state = {};
    }

    public componentDidMount() {
        let {node} = this.props;
        if (node.value) {
            let {value}=node;
            let team = +value.slice(0, 6);
            let task = value.slice(6, 8);
            let control = value.slice(8, 9);
            this.setState({defaultValues: {team, task, control}});
        }
    }

    public render() {
        let teamInputStyles = {
            border: 'none',
            width: '4em',
            'outline-offset': 0,
            'outline-color': 'transparent',
        };

        let taskInputStyles = {
            border: 'none',
            width: '2em',
            'outline-offset': 0,
            'outline-color': 'transparent',
        };

        let controlInputStyles = {
            border: 'none',
            width: '1em',
            'outline-offset': 0,
            'outline-color': 'transparent',
        };
        let containerStyles = {
                'font-size': '120%',
                border: '1px solid #ccc',
                'border-color': '#66afe9',
            }
            ;

        const onInputTask = (event)=> {
            let value = event.target.value.toLocaleUpperCase();
            this.setState(
                {
                    task: value,
                    validTask: this.isValidTask(value),
                    valid: this.isValid(this.getFullCode(null, value))
                }
            );
            if (this.isValidTask(value)) {
                jQuery(ReactDOM.findDOMNode(this.refs.control)).focus();
            }
        };

        const onInputTeam = (event)=> {
            let value = +event.target.value;
            this.setState({
                team: value,
                validTeam: this.isValidTeam(value),
                valid: this.isValid(this.getFullCode(value))
            });
            if (this.isValidTeam(event.target.value)) {
                jQuery(ReactDOM.findDOMNode(this.refs.task)).focus();
            }
        };

        const onInputControl = (event)=> {
            let value = +event.target.value;
            this.setState({
                control: value,
                valid: this.isValid(this.getFullCode(null, null, value))
            });

        };

        return (
            <div
                className={'row col-lg-6 task-code-container'}>
                <div
                    className={'form-control form-group has-feedback '}
                    style={containerStyles}>
                    <small>00</small>
                    <input
                        maxLength="4"
                        className={this.state.validTeam===false?'invalid':(this.state.validTeam===true?'valid':'')}
                        onInput={ onInputTeam }
                        style={teamInputStyles}
                        placeholder="XXXX"
                    />

                    <input
                        maxLength="2"
                        className={this.state.validTask===false?'invalid':(this.state.validTask===true?'valid':'')}
                        ref="task"
                        style={taskInputStyles}
                        placeholder="XX"
                        onInput={onInputTask}

                    />

                    <input
                        maxLength="1"
                        ref="control"
                        className={this.state.valid?'valid':'invalid'}
                        style={controlInputStyles}
                        placeholder="X"
                        onInput={onInputControl}
                    />
                    <span
                        className={'glyphicon '+( this.state.valid? 'glyphicon-ok':'') + ' form-control-feedback'}
                        aria-hidden="true"/>
                </div>
            </div>
        );
    };

    private getFullCode(team = null, task = null, control = null) {
        team = team || (+this.state.team < 1000) ? '0' + +this.state.team : +this.state.team;
        task = task || this.state.task || '';
        control = control || this.state.control || '';
        return '00' + team + task + control;
    }

    private isValid(code) {
        let subCode = code.split('').map((char)=> {
            return char.toLocaleUpperCase()
                .replace('A', 1)
                .replace('B', 2)
                .replace('C', 3)
                .replace('D', 4)
                .replace('E', 5)
                .replace('F', 6)
                .replace('G', 7)
                .replace('H', 8);
        });
        let c = 3 * (+subCode[0] + +subCode[3] + +subCode[6]) +
            7 * (+subCode[1] + +subCode[4] + +subCode[7]) +
            (+subCode[2] + +subCode[5] + +subCode[8]);
        return c % 10 == 0;
    }


    private isValidTask(task) {
        let {tasks} = this.props;
        return tasks.map(task=>task.label).indexOf(task) !== -1;
        // if (!task) {
        //     return false;
        // }
        // return /[A-H]{2}/.test(task);
    }

    private isValidTeam(team) {
        let {teams} = this.props;
        return teams.map(team=>team.team_id).indexOf(+team) !== -1;
    }

    public componentDidUpdate() {
        let code = this.getFullCode();

        this.props.node.value = this.getFullCode();
    }
}

jQuery('#taskcode').each(
    (a, input) => {
        let $ = jQuery;
        if (!input.value) {
            let c = document.createElement('div');
            let tasks = $(input).data('tasks');
            let teams = $(input).data('teams');
            $(input).parent().parent().append(c);
            $(input).parent().hide();
            ReactDOM.render(<TaskCode node={input} tasks={tasks} teams={teams}/>, c);
        }

    }
);