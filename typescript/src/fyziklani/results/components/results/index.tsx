import * as React from 'react';
import ResultsShower from '../../../helpers/components/results-shower';
import ResultsPresentation from './presentation/Index';
import PositionSwitcher from './presentation/PositionSwitcher';
import Settings from './presentation/settings';
import FilterSelect from './table/filters/select';
import ResultsTable from './table/index';

interface Props {
    mode: string;
}

export default class Results extends React.Component<Props, {}> {

    public render() {
        const {mode} = this.props;
        return <>{
                     (mode === 'presentation') ?
                         (<>
                             <Settings/>
                             <div className={'fixed-top h-100 w-100'} data-toggle="modal" data-target="#fyziklaniResultsOptionModal">
                                 <ResultsShower className={'inner-headline h-100 w-100'}>
                                     <ResultsPresentation/>
                                     <PositionSwitcher/>
                                 </ResultsShower>
                             </div>
                         </>) : (<div>
                             <FilterSelect mode={mode}/>
                             <ResultsShower className={null}>
                                 <ResultsTable/>
                             </ResultsShower>
                         </div>)
                 }</>;

    }
}
