import * as React from 'react';
import ResultsShower from '../../../helpers/components/results-shower';
import ResultsPresentation from './presentation/index';
import PositionSwitcher from './presentation/positionSwitcher';
import FilterSelect from './table/filters/select';
import ResultsTable from './table/index';

interface Props {
    mode: string;
}

export default class Results extends React.Component<Props, {}> {

    public render() {
        const {mode} = this.props;
        if (mode === 'presentation') {
            return <div data-toggle="modal" data-target="#fyziklaniResultsOptionModal">
                <ResultsShower className={'inner-headline'}>
                    <ResultsPresentation/>
                    <PositionSwitcher/>
                </ResultsShower>
            </div>;
        }
        return (
            <div>
                <FilterSelect mode={mode}/>
                <ResultsShower className={null}>
                    <ResultsTable/>
                </ResultsShower>
            </div>
        );

    }
}
