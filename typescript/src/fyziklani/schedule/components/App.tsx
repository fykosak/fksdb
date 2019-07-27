import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { lang } from '@i18n/i18n';
import DateDisplay from '../../../shared/components/displays/Date';
import {
    setVisibility,
    toggleChooser,
} from '../actions';
import { Store as ScheduleStore } from '../reducers';
import { Data } from './Index';
import Row from './Row';

interface Props {
    data: {
        data: Data;
        visible: boolean;
    };
    description: string;
    label: string;
}

interface State {
    showChooser?: boolean;

    onToggleChooser?(): void;

    onSetVisibility?(state: boolean): void;
}

class Schedule extends React.Component<Props & State, {}> {
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

const mapStateToProps = (store: ScheduleStore): State => {
    return {
        showChooser: store.compactValue.showChooser,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): State => {
    return {
        onSetVisibility: (state) => dispatch(setVisibility(state)),
        onToggleChooser: () => dispatch(toggleChooser()),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Schedule);
