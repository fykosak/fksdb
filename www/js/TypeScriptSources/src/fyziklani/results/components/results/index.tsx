import * as React from 'react';
import ResultsShower from '../../../helpers/components/results-shower';
import PositionSwitcher from './filter/PositionSwitcher';
import ResultsTable from './results-table';
import ResultsPresentation from './ResultsPresentation';

interface IProps {
    mode: string;
}

export default class Results extends React.Component<IProps, {}> {

    public render() {
        const {mode} = this.props;
        switch (mode) {
            case 'presentation':
                return <div data-toggle="modal" data-target="#fyziklaniResultsOptionModal">
                    <ResultsShower className={'inner-headline'}>
                        <ResultsPresentation/>
                    </ResultsShower>
                    <PositionSwitcher/>
                </div>;
            default:
            case 'view':
                return (
                    <div>
                        <ResultsShower className={null}>
                            <ResultsTable/>
                        </ResultsShower>
                    </div>
                );

        }
    }
}
