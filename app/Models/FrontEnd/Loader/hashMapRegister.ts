import { NetteActions } from 'FKSDB/Models/FrontEnd/Loader/netteActions';
import * as React from 'react';
import * as ReactDOM from 'react-dom';

export type mapRegisterCallback = (element: Element, reactId: string, data: string, actions: NetteActions) => void;

interface RegisteredComponent<O = any> {
    component: React.ComponentClass<O>;
    params: O;
}

interface RegisteredDataComponent<O = any, D = any> {
    component: React.ComponentClass<O & { data: D }>;
    params: O;
}

interface RegisteredActionComponent<O = any, D = any> {
    component: React.ComponentClass<O & { data: D; actions: NetteActions }>;
    params: O;
}

export default class HashMapLoader {
    private components: {
        [reactId: string]: RegisteredComponent;
    } = {};
    private actionsComponents: {
        [reactId: string]: RegisteredActionComponent;
    } = {};

    private dataComponents: {
        [reactId: string]: RegisteredDataComponent;
    } = {};
    private apps: {
        [reactId: string]: mapRegisterCallback;
    } = {};

    private keys: {
        [reactId: string]: boolean;
    } = {};

    public register(reactId: string, callback: mapRegisterCallback): void {
        this.checkConflict(reactId);
        this.apps[reactId] = callback;
    }

    public registerActionsComponent<T = any, P = {}>(
        reactId: string,
        component: React.ComponentClass<{ actions: NetteActions; data: T } & P>,
        params: P = null,
    ): void {
        this.checkConflict(reactId);
        this.actionsComponents[reactId] = {component, params};
    }

    public registerDataComponent<T = any, P = {}>(
        reactId: string,
        component: React.ComponentClass<{ data: T } & P>,
        params: P = null,
    ): void {
        this.checkConflict(reactId);
        this.dataComponents[reactId] = {component, params};
    }

    public registerComponent<P = {}>(
        reactId: string,
        component: React.ComponentClass<P>,
        params: P = null,
    ): void {
        this.checkConflict(reactId);
        this.components[reactId] = {component, params};
    }

    public render(element, reactId, rawData, actions): boolean {
        const data = JSON.parse(rawData);
        if (this.apps.hasOwnProperty(reactId)) {
            this.apps[reactId](element, reactId, rawData, actions);
            return true;
        }
        if (this.actionsComponents.hasOwnProperty(reactId)) {
            const {component, params} = this.actionsComponents[reactId];
            ReactDOM.render(React.createElement(component, {actions, data, ...params}), element);
            return true;
        }
        if (this.dataComponents.hasOwnProperty(reactId)) {
            const {component, params} = this.dataComponents[reactId];
            ReactDOM.render(React.createElement(component, {data, ...params}), element);
            return true;
        }
        if (this.components.hasOwnProperty(reactId)) {
            const {component, params} = this.components[reactId];
            ReactDOM.render(React.createElement(component, params), element);
            return true;
        }
        return false;
    }

    private checkConflict(reactId: string): void {
        if (this.keys.hasOwnProperty(reactId)) {
            throw new Error('App with "' + reactId + '" is already registred.');
        }
        this.keys[reactId] = true;
    }
}
