import * as React from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import logger from 'redux-logger';
import { config } from '../../../config';
import { app } from '../reducers';
import { INetteActions } from '../../../index';
import InputConnector from '../../../input-connector/compoenents';
import { IPrice } from '../../../shared/components/displays/price/interfaces';
import App from './app';
import { availableLanguages } from '../../../i18n/i18n';

interface IProps {
    data: IData;
    mode: string;
    actions: INetteActions;
    input: HTMLInputElement;
}

export type ILocalizedItem = {
    [lang in availableLanguages]?: ILocalizedScheduleItem;
};

export interface IInfo {
    description?: string;
    name: string;
}

export type ILocalizedInfo = {
    [lang in availableLanguages]?: IInfo;
};

export interface IScheduleItem extends ILocalizedItem {
    price: IPrice;
    id: number;
}

export interface ILocalizedScheduleItem {
    description: string;
    name: string;
    place: string;
}

export interface ISchedulePart {
    date: {
        start: string;
        end: string;
    };
    type: 'chooser' | 'info';
    descriptions?: ILocalizedInfo;
    parallels?: IScheduleItem[];
}

export interface IData {
    [key: string]: ISchedulePart;
}

const realp: IData = {
    pia1: {
        date: {
            end: '2019-02-16 19:00:00',
            start: '2019-02-16 24:00:00',
        },
        parallels: [
            {
                cs: {
                    description: 'Oficiálna recepcia pre všetkých registrovaných účastníkov a organizátorov ',
                    name: 'Recepcia',
                    place: 'Miesto sa ešte dorieši',
                },
                id: 0,
                price: {kc: 0, eur: 0},
            },
        ],
        type: 'chooser',
    },
    soPr1: {
        date: {
            end: '2019-02-16 11:30:00',
            start: '2019-02-16 09:00:00',
        },
        parallels: [
            {
                cs: {
                    description: '',
                    name: 'Fyzikálne prednášky na Karlove / na Tróji (ale chceme Karlov :!:)',
                    place: 'Karlov',
                },
                id: 1,
                price: {kc: 0, eur: 0},
            },
            {
                cs: {
                    description: '',
                    name: 'Fyzikálne prednášky na Karlove / na Tróji (ale chceme Karlov :!:)',
                    place: 'Karlov',
                },
                id: 2,
                price: {kc: 0, eur: 0},

            },
        ],
        type: 'chooser',
    },
    soPr2: {
        date: {
            end: '2019-02-16 11:30:00',
            start: '2019-02-16 09:00:00',
        },
        parallels: [
            {
                cs: {
                    description: '',
                    name: 'Fyzikálne prednášky na Karlove / na Tróji (ale chceme Karlov :!:)',
                    place: 'Karlov',
                },
                id: 3,
                price: {kc: 0, eur: 0},

            },
            {
                cs: {
                    description: '',
                    name: 'Fyzikálne prednášky na Karlove / na Tróji (ale chceme Karlov :!:)',
                    place: 'Karlov',
                },
                id: 4,
                price: {kc: 0, eur: 0},

            },
        ],
        type: 'chooser',
    },
    soTransfer1: {
        date: {
            end: '2019-02-16 12:00:00',
            start: '2019-02-16 13:00:00',
        },
        descriptions: {
            cs: {
                name: 'presun do Paladiuma',
            },
            en: {
                name: 'transfer to Paladium',
            },
        },
        type: 'info',
    },
    soLunch: {
        date: {
            end: '2019-02-16 12:00:00',
            start: '2019-02-16 13:00:00',
        },
        descriptions: {
            cs: {
                name: 'obed v Paladiumu',
            },
            en: {
                name: 'Lunch in Paladium',
            },
        },
        type: 'info',
    },
    soFree: {
        date: {
            end: '13:45:00',
            start: '16:15:00',
        },
        descriptions: {
            cs: {
                name: 'Free',
            },
            en: {
                name: 'Free',
            },
        },
        type: 'info',
    },
    soAfter: {
        date: {
            end: '2019-02-16 11:30:00',
            start: '2019-02-16 09:00:00',
        },
        parallels: [
            {
                cs: {
                    description: '',
                    name: 'Guided city tour po Prahe',
                    place: '???',
                },
                id: 5,
                price: {kc: 0, eur: 0},

            },
            {
                cs: {
                    description: '',
                    name: 'Národní technické muzeum',
                    place: '???',
                },
                id: 6,
                price: {kc: 0, eur: 0},

            },
        ],
        type: 'chooser',
    },

    soEvn: {
        date: {
            end: '2019-02-16 17:00:00',
            start: '2019-02-16 18:30:00',
        },
        parallels: [
            {
                cs: {
                    description: '',
                    name: 'Guided city tour po Prahe',
                    place: '???',
                },
                id: 5,
                price: {kc: 0, eur: 0},

            },
            {
                cs: {
                    description: '',
                    name: 'Národní technické muzeum',
                    place: '???',
                },
                id: 6,
                price: {kc: 0, eur: 0},

            },
        ],
        type: 'chooser',
    },
    soNight: {
        date: {
            end: '2019-02-16 18:30:00',
            start: '2019-02-16 24:00:00',
        },
        descriptions: {
            cs: {
                name: 'Voľný program',
            },
            en: {
                name: 'Voľný program',
            },
        },
        type: 'info',
    },
    neExcr: {
        date: {
            end: '2019-02-17 09:30:00',
            start: '2019-02-17 11:30:00',
        },
        parallels: [
            {
                cs: {
                    description: '',
                    name: 'Exkurzie – Karlov #1',
                    place: '???',
                },
                id: 5,
                price: {kc: 0, eur: 0},

            },
            {
                cs: {
                    description: '',
                    name: 'Exkurzie – Karlov #',
                    place: '???',
                },
                id: 6,
                price: {kc: 0, eur: 0},

            },
        ],
        type: 'chooser',
    },
    neEnd: {
        date: {
            end: '2019-02-17 11:30:00',
            start: '2019-02-17 12:00:00',
        },
        descriptions: {
            cs: {
                name: 'Oficiálne ukončenie',
            },
            en: {
                name: 'End',
            },
        },
        type: 'info',
    },
};


export default class Index extends React.Component<IProps, {}> {

    public render() {
        const store = !config.dev ? createStore(app, applyMiddleware(logger)) : createStore(app);

        return (
            <Provider store={store}>
                <>
                    <InputConnector input={this.props.input}/>
                    <App data={realp}/>
                </>
            </Provider>
        );
        // <InputConnector input={this.props.input}/>
    }
}
