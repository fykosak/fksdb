import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import { lang } from '../../../i18n/i18n';
import DateDisplay from '../../../shared/components/displays/date';
import {
    setVisibility,
    toggleChooser,
} from '../actions';
import { IFyziklaniScheduleStore } from '../reducers';
import { IData } from './index';
import Row from './row';

interface IProps {
    data: {
        data: IData;
        visible: boolean;
    };
    description: string;
    label: string;
}

interface IState {
    showChooser?: boolean;

    onToggleChooser?(): void;

    onSetVisibility?(state: boolean): void;
}

class Schedule extends React.Component<IProps & IState, {}> {
    public componentDidMount() {
        this.props.onSetVisibility(this.props.data.visible);
    }

    public render() {
        const {data: {data}, showChooser, label, description, onToggleChooser} = this.props;
        const rows = [];
        let lastBlockDay = null;
        for (const blockName in data) {
            if (data.hasOwnProperty(blockName)) {

                const blockData = data[blockName];
                const startBlockDay = (new Date(blockData.date.start)).getDay();
                if (lastBlockDay !== startBlockDay) {
                    rows.push(<div key={startBlockDay} className={'schedule-row schedule-row-weekday row'}>
                        <h3>
                            <DateDisplay date={blockData.date.start} options={{weekday: 'long'}}/>
                        </h3>
                    </div>);
                }
                lastBlockDay = startBlockDay;
                rows.push(<Row key={blockName} blockData={blockData} blockName={blockName}/>);
            }
        }
        return (
            <div className={'bd-callout bd-callout-fyziklani'}>
                <h4>{label}</h4>
                <p className={'text-muted mb-3'} dangerouslySetInnerHTML={{__html: description}}/>
                {showChooser && (<div className={'schedule-field-container mb-3'}>
                    {rows}
                </div>)}
                <div className={'text-center'}>
                    <button
                        className={'btn btn-fyziklani btn-block'}
                        onClick={(event) => {
                            event.preventDefault();
                            onToggleChooser();
                        }}
                    >{showChooser ? lang.getText('Hide schedule') : lang.getText('Show schedule')}
                    </button>
                </div>
            </div>
        );
    }
}

const mapStateToProps = (store: IFyziklaniScheduleStore): IState => {
    return {
        showChooser: store.compactValue.showChooser,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<IFyziklaniScheduleStore>): IState => {
    return {
        onSetVisibility: (state) => dispatch(setVisibility(state)),
        onToggleChooser: () => dispatch(toggleChooser()),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Schedule);
