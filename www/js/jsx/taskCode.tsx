class TaskCode extends React.Component<any,any> {
    constructor() {
        super();
        this.state = {};
    }

    public componentDidMount() {
        // focus on load
        jQuery(ReactDOM.findDOMNode(this.refs.team)).focus();
    }

    public render() {
        const onInputTask = (event)=> {
            let value = event.target.value.toLocaleUpperCase();
            this.isValid(this.getFullCode(null, value));
            if (this.isValidTask(value)) {
                jQuery(ReactDOM.findDOMNode(this.refs.control)).focus();
            }
            this.setState(
                {
                    task: value,
                }
            );
        };

        const onInputTeam = (event)=> {
            let value = +event.target.value;
            this.isValid(this.getFullCode(value));
            if (this.isValidTeam(value)) {
                jQuery(ReactDOM.findDOMNode(this.refs.task)).focus();
            }
            this.setState({
                team: value,
            });
        };

        const onInputControl = (event)=> {
            let value = +event.target.value;
            console.log(value);
            this.isValid(this.getFullCode(null, null, value));
            this.setState({
                control: value,
            });
        };

        return (
            <div
                className={'task-code-container'}>
                <div
                    className={'form-control has-feedback '}>
                    <small style={{paddingRight:'1em'}}>00</small>
                    <input
                        maxLength="4"
                        ref="team"
                        className={'team '+(this.state.validTeam===false?'invalid':(this.state.validTeam===true?'valid':''))}
                        onKeyUp={ onInputTeam }
                        placeholder="XXXX"
                    />

                    <input
                        maxLength="2"
                        className={'task '+(this.state.validTask===false?'invalid':(this.state.validTask===true?'valid':''))}
                        ref="task"
                        placeholder="XX"
                        onInput={onInputTask}
                    />
                    <input
                        maxLength="1"
                        ref="control"
                        className={'control '+(this.state.valid?'valid':'invalid')}
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
        control = (control !== null) ? control : (this.state.control || '');
        return '00' + team + task + control;
    }

    private isValid(code) {
        let {validTeam, validTask}= this.state;
        if (!validTask) {
            this.state.valid = false;
            return;
        }
        if (!validTeam) {
            this.state.valid = false;
            return;
        }
        console.log(code);
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
        this.state.valid = c % 10 == 0;
    }


    private isValidTask(task) {
        let {tasks} = this.props;
        return this.state.validTask = tasks.map(task=>task.label).indexOf(task) !== -1;
    }

    private isValidTeam(team) {
        let {teams} = this.props;
        return this.state.validTeam = teams.map(team=>team.team_id).indexOf(+team) !== -1;
    }

    public componentDidUpdate() {
        this.props.node.value = '';
        if (this.state.valid) {
            this.props.node.value = this.getFullCode();
        }
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
            $(c).addClass('col-lg-6');
            ReactDOM.render(<TaskCode node={input} tasks={tasks} teams={teams}/>, c);
        }
    }
);