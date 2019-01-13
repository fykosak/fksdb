import * as React from 'react';
import ResultsShower from '../../../helpers/components/results-shower';
import ResultsPresentation from './presentation/index';
import PositionSwitcher from './presentation/positionSwitcher';
import Settings from './presentation/settings';
import FilterSelect from './table/filters/select';
import ResultsTable from './table/index';

interface Props {
    mode: string;
}

export default class Results extends React.Component<Props, {}> {

    public render() {
        const {mode} = this.props;
        switch (mode) {
            case 'presentation':
                return <div data-toggle="modal" data-target="#fyziklaniResultsOptionModal">
                    <Settings/>
                    <ResultsShower className={'inner-headline'}>
                        <ResultsPresentation/>
                        <PositionSwitcher/>
                    </ResultsShower>
                </div>;
            default:
            case 'view':
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
}
