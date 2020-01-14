import { ScheduleItemDef } from '../../events/schedule/middleware/interfaces';

export interface PaymentScheduleItem {
    label: string;
    id: number;
    hasPayment: boolean;
    scheduleItem: ScheduleItemDef;
    personId: number;
    personName: string;
    personFamilyName: string;
}
