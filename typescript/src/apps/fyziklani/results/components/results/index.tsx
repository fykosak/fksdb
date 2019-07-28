import * as React from 'react';
import ResultsShower from '../../../helpers/components/resultsShower/index';
import ResultsPresentation from './presentation/';
import PositionSwitcher from './presentation/positionSwitcher';
import Settings from './presentation/settings/';
import ResultsTable from './table/';
import FilterSelect from './table/filters/select/';

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
