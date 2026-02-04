import { DEFAULT_LAYOUT } from '@/router/constants';

export default {
  path: '/finance',
  name: 'finance',
  component: DEFAULT_LAYOUT,
  meta: {
    locale: 'menu.finance',
    icon: 'icon-finance',
    requiresAuth: true,
    order: 3,
  },
  children: [
    {
      path: 'bill',
      name: 'FinanceBill',
      component: () => import('@/views/finance/bill/list.vue'),
      meta: {
        locale: 'menu.finance.bill',
        requiresAuth: true,
      },
    },
    {
      path: 'bill/order-summary',
      name: 'FinanceBillOrderSummary',
      component: () => import('@/views/finance/bill/order-summary.vue'),
      meta: {
        locale: 'menu.finance.bill.orderSummary',
        requiresAuth: true,
      },
    },
    {
      path: 'bill/repayment-plan-detail',
      name: 'FinanceBillRepaymentPlanDetail',
      component: () => import('@/views/finance/bill/repayment-plan-detail.vue'),
      meta: {
        locale: 'menu.finance.bill.repaymentPlanDetail',
        requiresAuth: true,
      },
    },
    {
      path: 'payment',
      name: 'FinancePayment',
      component: () => import('@/views/finance/payment/list.vue'),
      meta: {
        locale: 'menu.finance.payment',
        requiresAuth: true,
      },
    },
    {
      path: 'disbursement',
      name: 'FinanceDisbursement',
      component: () => import('@/views/finance/disbursement/list.vue'),
      meta: {
        locale: 'menu.finance.disbursement',
        requiresAuth: true,
      },
    },
    {
      path: 'application',
      name: 'FinanceApplication',
      component: () => import('@/views/finance/application/list.vue'),
      meta: {
        locale: 'menu.finance.application',
        requiresAuth: true,
      },
    },
  ],
};
