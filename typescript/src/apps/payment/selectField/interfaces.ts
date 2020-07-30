import { ScheduleItemDef } from '../../events/schedule/interfaces';

export interface PaymentScheduleItem {
    label: string;
    id: number;
    hasPayment: boolean;
    scheduleItem: ScheduleItemDef;
    personId: number;
    personName: string;
    personFamilyName: string;
}
