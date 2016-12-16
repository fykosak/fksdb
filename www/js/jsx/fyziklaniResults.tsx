//import * as React from './lib/react.min';

interface ISubmit {
    points: number;
    task_id: number;
    team_id: number;
}
interface ITeam {
    category: string;
    name: string;
    room?: string;
    team_id: number;
}
interface ITask {
    label: string;
    task_id: number;
    name: string;
}

interface IResultsState {
    displayCategory: string;
    displayRoom: string;
    image: string;
    submits: Array<any>;
    times: any;
    tasks: Array<ITask>;
    teams: Array<any>;
    visible: boolean;
}

const filters = [
    {room: null, category: null, name: "ALL"},
    {room: null, category: 'A', name: "A"},
    {room: null, category: 'B', name: "B"},
    {room: null, category: 'C', name: "C"},
    {room: 'M1', category: null, name: "M1"},
    {room: 'M2', category: null, name: "M2"},
    {room: 'M3', category: null, name: "M3"},
    {room: 'M4', category: null, name: "M4"},
    {room: 'M5', category: null, name: "M5"},
    {room: 'F1', category: null, name: "F1"},
    {room: 'F2', category: null, name: "F2"},
    {room: 'S1', category: null, name: "S1"},
    {room: 'S2', category: null, name: "S2"},
    {room: 'S6', category: null, name: "S6"},
];

const store = {};

class Results extends React.Component<void, IResultsState> {
    componentDidMount() {
        let that = this;
        console.log('mount');
        $.nette.ajax({
            data: {
                type: 'init'
            },
            success: data=> {
                let {tasks, teams} = data;
                that.state.tasks = tasks;
                that.state.teams = teams;
            },
            error: ()=> alert('error!')
        });


        setInterval(()=> {
            $.nette.ajax({
                data: {
                    type: 'refresh'
                },
                success: (data)=> {
                    let {times, submits} = data;
                    this.setState({submits, times});
                    this.forceUpdate();
                }
            });
        }, 10 * 1000);
    }

    public constructor() {
        super();
        this.state = {
            autoDisplayCategory: null,
            autoDisplayRoom: null,
            autoSwitch: false,
            hardVisible: false,
            displayCategory: null,
            displayRoom: null,
            image: null,
            submits: [],
            times: {},
            tasks: new Array<ITask>(),
            teams: [],
            visible: false,
        };
    }

    public render() {
        let {times:{visible}, hardVisible}=this.state;
        this.state.visible = visible || hardVisible;


        let filtersButtons = filters.map((filter, index)=> {
            return (
                <li key={index} role="presentation" className={(filter.room==this.state.displayRoom&&filter.category==this.state.displayCategory)?'active':''}>
                    <a onClick={()=>{
                    this.setState({displayCategory:filter.category,
                    displayRoom:filter.room});
                    }}>
                        {filter.name}
                    </a>
                </li>
            )
        });
        let {} = this.state;

        return (<div>
            <ul className="nav nav-tabs" style={{display:(this.state.visible)?'':'none'}}>
                {filtersButtons}
            </ul>

            <Images {...this.state} {...this.props}/>
            <ResultsTable {...this.state} {...this.props}/>
            <Timer {...this.state} {...this.props}/>

            <button className="btn btn-default">
                <span className="glyphicon glyphicon-cog" type="button"/>
                Nastavenia
            </button>
            <div id="resultsOpt">
                <div className="form-group">
                    <label className="sr-only">
                        <span>Místnost</span>
                    </label>
                    <select className="form-control" onChange={(event)=>{
                        this.setState({autoDisplayRoom: event.target.value});
                        }}>
                        <option >--vyberte miestnosť--</option>
                        {                                filters
                            .filter((filter)=>filter.room != null)
                            .map((filter, index)=> {
                                return (<option key={index} value={filter.room}>{filter.name}</option>)
                            })
                        }
                    </select>
                </div>

                <div className="form-group">
                    <label className="sr-only">
                        <span>Categorie</span>
                    </label>
                    <select className="form-control" onChange={(event)=>{
                        this.setState({autoDisplayCategory: event.target.value});
                        }}>
                        <option value>--vyberte kategoriu--</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                    </select>
                </div>

                <div className="form-group">
                    <div className="checkbox">
                        <label>
                            <input type="checkbox" value="1" onChange={(event)=>{
                            this.setState({autoSwitch:event.target.checked});
                            }}/>
                            <span>Automatické prepíanie miestností a kategoríi</span>
                        </label>

                    </div>
                </div>
                <div className="form-group has-error">
                    <div className="checkbox">
                        <label>
                            <input type="checkbox" value="1" onChange={(event)=>{
                            this.setState({hardVisible:event.target.checked});
                            }}/>
                            Neverejné výsledkovky, <span className="text-danger">túto funkciu nezapínajte pokial sú vysledkovky premietané!!!</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>);
    }
}

class ResultsTable extends React.Component<any, void> {
    public constructor() {
        super();
        this.refs = {table: undefined};
    }

    componentDidUpdate() {
        let $table = $(ReactDOM.findDOMNode(this.refs.table));
        try {
            $table.trigger("update");
            $table.trigger("sorton", [[[1, 1]]]);
        } catch (error) {
            console.error(error);
        }
    }

    componentDidMount() {
        let $table = $(ReactDOM.findDOMNode(this.refs.table));
        $table.tablesorter()
    }

    public render() {
        let {} = this.props;

        let rows = [];

        let {submits, teams, tasks, displayCategory, displayRoom} = this.props;

        teams.forEach((team: ITeam, teamIndex) => {
            let cools = [];
            tasks.forEach((task: ITask, taskIndex)=> {
                let submit: ISubmit = submits.filter((submit: ISubmit)=> {
                    return submit.task_id == task.task_id && submit.team_id == team.team_id;
                })[0];
                let points = submit ? submit.points : '';
                cools.push(<td data-points={points} key={taskIndex}>{points}</td>);
            });

            let styles = {
                display: ((!displayCategory || displayCategory == team.category) && (!displayRoom || displayRoom == team.room)) ? '' : 'none',
            };
            let count = 0;
            let sum = submits.filter((submit: ISubmit)=> {
                return submit.team_id == team.team_id;
            }).reduce((val, submit: ISubmit)=> {
                count++;
                return val + +submit.points;
            }, 0);
            let average = count > 0 ? Math.round(sum / count * 100) / 100 : '-';
            rows.push(<tr key={teamIndex} style={styles}>
                <td>{team.name}</td>
                <td className="sum">{sum}</td>
                <td>{count}</td>
                <td>{average}</td>
                {cools}
            </tr>);


        });
        let headCools = [];
        tasks.forEach((task: ITask, taskIndex)=> {
            headCools.push(<th key={taskIndex} data-task_label={task.label}>{task.label}</th>);
        });

        return (
            <div style={{display: (this.props.visible ? 'block' : 'none')}}>
                <table ref="table" className="tablesorter">
                    <thead>
                    <tr>
                        <th/>
                        <th>Sum</th>
                        <th>Prů</th>
                        <th>Q</th>
                        {headCools}
                    </tr>
                    </thead>
                    <tbody>
                    {rows}
                    </tbody>
                </table>
            </div>
        )
    };
}

class Timer extends React.Component<any, any> {
    public constructor() {
        super();
        this.state = {toStart: 0, toEnd: 0};
    }

    public componentDidMount() {
        setInterval(()=> {
            this.state.toStart = this.state.toStart - 1;
            this.state.toEnd = this.state.toEnd - 1;
            this.forceUpdate();
        }, 1000);
    }

    public componentWillReceiveProps() {
        let {times:{toStart, toEnd}} = this.props;
        this.state.toStart = toStart;
        this.state.toEnd = toEnd;
    }


    public render() {
        let {toStart, toEnd}=this.state;
        let timeStamp = 0;
        if (toStart > 0) {
            timeStamp = toStart * 1000;
        } else if (toEnd > 0) {
            timeStamp = toEnd * 1000;
        } else {
            return (<div/>);
        }
        let date = new Date(timeStamp);
        let h = date.getUTCHours();
        let m = date.getUTCMinutes();
        let s = date.getUTCSeconds();
        return (
            <div className={'clock '+(this.props.visible?'':'big')}>
                {
                    (h < 10 ? "0" + h : "" + h)
                    + ":" +
                    ( m < 10 ? "0" + m : "" + m)
                    + ":" +
                    (s < 10 ? "0" + s : "" + s)
                }
            </div>);
    }
}
class Images extends React.Component<any,any> {
    public constructor() {
        super();
        this.state = {toStart: 0, toEnd: 0};
    }

    public componentWillReceiveProps() {
        let {times:{toStart, toEnd}} = this.props;
        this.state.toStart = toStart;
        this.state.toEnd = toEnd;
    }

    public render() {

        let {toStart, toEnd}=this.state;

        if (toStart == 0 || toEnd == 0) {
            return (<div/>);
        }
        const basePath = $(document.getElementsByClassName('fyziklani-results')[0]).data('basepath');
        let imgSRC = basePath + '/images/fyziklani/';
        if (toStart > 300) {
            imgSRC += 'nezacalo.svg';
        } else if (toStart > 0) {
            imgSRC += 'brzo.svg';
        } else if (toStart > -120) {
            imgSRC += 'start.svg';
        } else if (toEnd > 0) {
            imgSRC += 'fyziklani.svg';

        } else if (toEnd > -240) {
            imgSRC += 'skoncilo.svg';
        } else {
            imgSRC += 'ceka.svg';

        }
        return (
            <div style={{display:this.props.visible?'none':''}} id='imageWP' data-basepath={basePath}>
                <img src={imgSRC} alt=""/>
            </div>)


    }


}

ReactDOM.render(<Results/>, document.getElementsByClassName('fyziklani-results')[0]);

/*

 * Zobrazí všetky riadky tabuľky;
 * @returns {undefined}

 const disableFilter = function () {
 $nav.find('li').removeClass('active');
 $nav.find('li[data-type="all"]').addClass('active');
 $tBody.find('tr').show();

 };
 /**
 * Zobrazí riadky tabuľky kde sa kategoria zhoduje so zadanou kategoriou;
 * @param {String} category
 * @author Michal Červeňák
 * @returns {undefined}

 const applyFilterByCategory = function (category) {
 $nav.find('li').removeClass('active');
 $nav.find('li[data-category="' + category + '"]').addClass('active');
 $tBody.find('tr').each(function () {
 if ($(this).data('category') == category) {
 $(this).show();
 } else {
 $(this).hide();
 }
 });
 };
 /**
 * Zobrazí riadky tabuľky kde sa miestnost zhoduje so zadanou miestnostou;
 * @param {String} room
 * @author Michal Červeňák
 * @returns {undefined}

 const applyFilterByRoom = function (room) {
 $nav.find('li').removeClass('active');
 $nav.find('li[data-room="' + room + '"]').addClass('active');
 $tBody.find('tr').each(function () {
 if ($(this).data('room') == room) {
 $(this).show();
 } else {
 $(this).hide();
 }
 });
 };
 $nav.find('li').click(function () {
 if ($form.find('#autoSwitch').is(':checked')) {
 return;
 }
 if ($(this).data('room')) {
 applyFilterByRoom($(this).data('room'));
 } else if ($(this).data('category')) {
 applyFilterByCategory($(this).data('category'));
 } else {
 disableFilter();
 }
 });
 var i = 0;
 var applyNext = function () {
 //   console.debug('filter' + i);
 var t = 15000;
 if ($form.find('#autoSwitch').is(':checked') && $table.is(':visible')) {
 $("html, body").animate({scrollTop: 0}, 0);
 switch (i) {
 case 0: {
 t = 30000;
 disableFilter();
 break;
 }
 case 1: {
 var room = $form.find('#room').val();
 //   console.debug(room);
 if (room) {
 applyFilterByRoom(room);
 } else {
 t = 0;
 }
 break;
 }
 case 2: {
 var category = $form.find('#category').val();
 if (category) {
 applyFilterByCategory(category);
 } else {
 t = 0;
 }
 break;
 }
 }
 if (t > 1000) {
 $("html, body").delay(t / 3).animate({scrollTop: $(document).height()}, t / 3);
 }
 }
 setTimeout(function () {
 i++;
 i = i % 3;
 applyNext();
 }, t);
 };
 applyNext();


 };



*/




